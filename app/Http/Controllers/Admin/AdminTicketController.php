<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminTicketController extends Controller
{
    /**
     * Dashboard des tickets
     */
    public function index(Request $request)
    {
        $stats = [
            'total' => Ticket::count(),
            'open' => Ticket::where('status', 'open')->count(),
            'in_progress' => Ticket::where('status', 'in_progress')->count(),
            'urgent' => Ticket::where('priority', 'urgent')->count(),
            'resolved_today' => Ticket::whereDate('resolved_at', today())->count(),
            'avg_response_time' => $this->getAverageResponseTime(),
        ];

        $query = Ticket::with(['user', 'assignedTo'])
            ->withCount('replies');

        // Filtres
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->has('priority') && $request->priority !== 'all') {
            $query->where('priority', $request->priority);
        }

        if ($request->has('assigned_to') && $request->assigned_to !== 'all') {
            if ($request->assigned_to === 'unassigned') {
                $query->whereNull('assigned_to');
            } else {
                $query->where('assigned_to', $request->assigned_to);
            }
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('ticket_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $tickets = $query->orderBy('created_at', 'desc')->paginate(20);

        // Liste des admins pour assignation
        $admins = User::where('is_admin', true)->get();

        return view('admin.tickets.index', compact('stats', 'tickets', 'admins'));
    }

    /**
     * Voir un ticket (admin)
     */
    public function show(Ticket $ticket)
    {
        $ticket->load([
            'replies' => function($query) {
                $query->orderBy('created_at', 'asc')->with('user');
            },
            'attachments',
            'user',
            'assignedTo'
        ]);

        $admins = User::where('is_admin', true)->get();

        return view('admin.tickets.show', compact('ticket', 'admins'));
    }

    /**
     * Répondre à un ticket (admin)
     */
    public function reply(Request $request, Ticket $ticket)
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
            'is_internal' => ['boolean'],
            'change_status' => ['nullable', 'in:open,in_progress,waiting,resolved,closed'],
        ]);

        TicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id' => $request->user()->id,
            'message' => $data['message'],
            'is_internal' => $data['is_internal'] ?? false,
        ]);

        // Changer le statut si demandé
        if (!empty($data['change_status'])) {
            $updateData = ['status' => $data['change_status']];
            
            if ($data['change_status'] === 'resolved') {
                $updateData['resolved_at'] = now();
            }
            
            if ($data['change_status'] === 'closed') {
                $updateData['closed_at'] = now();
            }
            
            $ticket->update($updateData);
        }

        return back()->with('success', 'Réponse ajoutée avec succès !');
    }

    /**
     * Assigner un ticket
     */
    public function assign(Request $request, Ticket $ticket)
    {
        $data = $request->validate([
            'assigned_to' => ['nullable', 'exists:users,id'],
        ]);

        $ticket->update([
            'assigned_to' => $data['assigned_to'],
            'status' => $data['assigned_to'] ? 'in_progress' : 'open',
        ]);

        return back()->with('success', 'Ticket assigné avec succès !');
    }

    /**
     * Changer le statut
     */
    public function updateStatus(Request $request, Ticket $ticket)
    {
        $data = $request->validate([
            'status' => ['required', 'in:open,in_progress,waiting,resolved,closed'],
        ]);

        $updateData = ['status' => $data['status']];

        if ($data['status'] === 'resolved') {
            $updateData['resolved_at'] = now();
        }

        if ($data['status'] === 'closed') {
            $updateData['closed_at'] = now();
        }

        $ticket->update($updateData);

        return back()->with('success', 'Statut mis à jour avec succès !');
    }

    /**
     * Changer la priorité
     */
    public function updatePriority(Request $request, Ticket $ticket)
    {
        $data = $request->validate([
            'priority' => ['required', 'in:low,medium,high,urgent'],
        ]);

        $ticket->update(['priority' => $data['priority']]);

        return back()->with('success', 'Priorité mise à jour avec succès !');
    }

    /**
     * Supprimer un ticket
     */
    public function destroy(Ticket $ticket)
    {
        $ticket->delete();

        return redirect()->route('admin.tickets.index')
            ->with('success', 'Ticket supprimé avec succès !');
    }

    /**
     * Statistiques
     */
    public function stats()
    {
        // Tickets par statut
        $ticketsByStatus = Ticket::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get();

        // Tickets par priorité
        $ticketsByPriority = Ticket::select('priority', DB::raw('COUNT(*) as count'))
            ->groupBy('priority')
            ->get();

        // Tickets par catégorie
        $ticketsByCategory = Ticket::select('category', DB::raw('COUNT(*) as count'))
            ->groupBy('category')
            ->get();

        // Tickets résolus par jour (30 derniers jours)
        $resolvedPerDay = Ticket::select(
            DB::raw('DATE(resolved_at) as date'),
            DB::raw('COUNT(*) as count')
        )
            ->whereNotNull('resolved_at')
            ->where('resolved_at', '>=', now()->subDays(30))
            ->groupBy(DB::raw('DATE(resolved_at)'))
            ->orderBy('date')
            ->get();

        // Top agents
        $topAgents = User::select('users.id', 'users.name')
            ->selectRaw('COUNT(tickets.id) as tickets_resolved')
            ->join('tickets', 'users.id', '=', 'tickets.assigned_to')
            ->where('tickets.status', 'resolved')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('tickets_resolved')
            ->limit(10)
            ->get();

        return view('admin.tickets.stats', compact(
            'ticketsByStatus',
            'ticketsByPriority',
            'ticketsByCategory',
            'resolvedPerDay',
            'topAgents'
        ));
    }

    /**
     * Calculer le temps de réponse moyen
     */
    private function getAverageResponseTime()
    {
        $avgMinutes = DB::table('tickets')
            ->join('ticket_replies', 'tickets.id', '=', 'ticket_replies.ticket_id')
            ->whereNotNull('tickets.assigned_to')
            ->selectRaw('AVG(EXTRACT(EPOCH FROM (ticket_replies.created_at - tickets.created_at)) / 60) as avg_minutes')
            ->value('avg_minutes');

        if (!$avgMinutes) {
            return 'N/A';
        }

        $hours = floor($avgMinutes / 60);
        $minutes = round($avgMinutes % 60);

        return $hours > 0 ? "{$hours}h {$minutes}min" : "{$minutes}min";
    }
}