<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiConversation;
use App\Models\AiMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminAiController extends Controller
{
    /**
     * Dashboard principal
     */
    public function index()
    {
        $stats = [
            'total_conversations' => AiConversation::count(),
            'total_messages' => AiMessage::count(),
            'conversations_today' => AiConversation::whereDate('created_at', today())->count(),
            'messages_today' => AiMessage::whereDate('created_at', today())->count(),
            'by_assistant' => AiConversation::select('assistant', DB::raw('count(*) as total'))
                ->groupBy('assistant')
                ->get()
                ->pluck('total', 'assistant'),
            'avg_messages_per_conversation' => round(
                AiMessage::count() / max(AiConversation::count(), 1), 
                1
            ),
        ];

        // Dernières conversations
        $recentConversations = AiConversation::with(['messages' => function($query) {
            $query->latest()->limit(1);
        }])
            ->withCount('messages')
            ->latest()
            ->limit(10)
            ->get();

        // Graphique des 7 derniers jours
        $chartData = AiConversation::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('count(*) as count')
        )
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('admin.ai.index', compact('stats', 'recentConversations', 'chartData'));
    }

    /**
     * Liste des conversations avec filtres
     */
    public function conversations(Request $request)
    {
        $query = AiConversation::with('messages')->withCount('messages');

        // Filtres
        if ($request->has('assistant') && $request->assistant !== 'all') {
            $query->where('assistant', $request->assistant);
        }

        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->whereHas('messages', function($q) use ($search) {
                $q->where('content', 'like', "%{$search}%");
            });
        }

        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Tri
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $conversations = $query->paginate(20)->withQueryString();

        return view('admin.ai.conversations', compact('conversations'));
    }

    /**
     * Détails d'une conversation
     */
    public function show(AiConversation $conversation)
    {
        $conversation->load(['messages' => function($query) {
            $query->orderBy('created_at', 'asc');
        }]);

        // Statistiques de la conversation
        $stats = [
            'total_messages' => $conversation->messages->count(),
            'user_messages' => $conversation->messages->where('role', 'user')->count(),
            'assistant_messages' => $conversation->messages->where('role', 'assistant')->count(),
            'duration' => $conversation->created_at->diffForHumans($conversation->updated_at),
            'avg_response_length' => round(
                $conversation->messages->where('role', 'assistant')->avg(function($msg) {
                    return strlen($msg->content);
                }), 
                0
            ),
        ];

        return view('admin.ai.show', compact('conversation', 'stats'));
    }

    /**
     * Supprimer une conversation
     */
    public function destroy(AiConversation $conversation)
    {
        $conversation->delete();

        return redirect()->route('admin.ai.conversations')
            ->with('success', 'Conversation supprimée avec succès');
    }

    /**
     * Supprimer plusieurs conversations
     */
    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids', []);
        AiConversation::whereIn('id', $ids)->delete();

        return back()->with('success', count($ids) . ' conversation(s) supprimée(s)');
    }

    /**
     * Exporter les données
     */
    public function export(Request $request)
    {
        $format = $request->get('format', 'csv');

        $conversations = AiConversation::with('messages')
            ->when($request->has('assistant'), function($q) use ($request) {
                $q->where('assistant', $request->assistant);
            })
            ->get();

        if ($format === 'json') {
            return response()->json($conversations);
        }

        // Export CSV
        $filename = 'ai_conversations_' . now()->format('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function() use ($conversations) {
            $file = fopen('php://output', 'w');
            
            // En-têtes
            fputcsv($file, ['ID', 'User ID', 'Assistant', 'Messages Count', 'Created At', 'Last Message']);

            foreach ($conversations as $conv) {
                fputcsv($file, [
                    $conv->id,
                    $conv->user_id,
                    $conv->assistant,
                    $conv->messages->count(),
                    $conv->created_at->format('Y-m-d H:i:s'),
                    $conv->messages->last()?->content ?? '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Statistiques avancées
     */
    public function stats()
    {
        // Stats par assistant - CORRIGÉ POUR POSTGRESQL
        $statsByAssistant = AiConversation::select('assistant')
            ->selectRaw('COUNT(*) as conversations')
            ->selectRaw('COALESCE(SUM((SELECT COUNT(*) FROM ai_messages WHERE ai_messages.ai_conversation_id = ai_conversations.id)), 0) as messages')
            ->groupBy('assistant')
            ->get();

        // Alternative avec JOIN (meilleure performance)
        // $statsByAssistant = AiConversation::select('assistant')
        //     ->selectRaw('COUNT(DISTINCT ai_conversations.id) as conversations')
        //     ->selectRaw('COUNT(ai_messages.id) as messages')
        //     ->leftJoin('ai_messages', 'ai_conversations.id', '=', 'ai_messages.ai_conversation_id')
        //     ->groupBy('assistant')
        //     ->get();

        // Conversations par jour (30 derniers jours)
        $conversationsPerDay = AiConversation::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count')
        )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();

        // Messages par heure
        $messagesByHour = AiMessage::select(
            DB::raw('EXTRACT(HOUR FROM created_at) as hour'),
            DB::raw('COUNT(*) as count')
        )
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy(DB::raw('EXTRACT(HOUR FROM created_at)'))
            ->orderBy('hour')
            ->get();

        // Top utilisateurs
        $topUsers = AiConversation::select('user_id')
            ->selectRaw('COUNT(*) as conversations')
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->orderByDesc('conversations')
            ->limit(10)
            ->with('user')
            ->get();

        return view('admin.ai.stats', compact(
            'statsByAssistant',
            'conversationsPerDay',
            'messagesByHour',
            'topUsers'
        ));
    }
}