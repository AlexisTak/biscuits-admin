<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\TicketAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TicketController extends Controller
{
    /**
     * Liste des tickets du client
     */
    public function index(Request $request)
    {
        $query = Ticket::where('user_id', $request->user()->id)
            ->with(['replies', 'assignedTo'])
            ->withCount('replies');

        // Filtres
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->has('priority') && $request->priority !== 'all') {
            $query->where('priority', $request->priority);
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('ticket_number', 'like', "%{$search}%");
            });
        }

        $tickets = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('tickets.index', compact('tickets'));
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        return view('tickets.create');
    }

    /**
     * Créer un nouveau ticket
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'category' => ['required', 'in:technical,billing,feature_request,bug,other'],
            'attachments.*' => ['nullable', 'file', 'max:10240'], // 10MB max par fichier
        ]);

        $ticket = Ticket::create([
            'user_id' => $request->user()->id,
            'subject' => $data['subject'],
            'description' => $data['description'],
            'priority' => $data['priority'],
            'category' => $data['category'],
            'status' => 'open',
        ]);

        // Gérer les fichiers joints
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $filename = uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('tickets/' . $ticket->id, $filename, 'private');

                TicketAttachment::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => $request->user()->id,
                    'filename' => $filename,
                    'original_filename' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'path' => $path,
                ]);
            }
        }

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Ticket créé avec succès ! Numéro : ' . $ticket->ticket_number);
    }

    /**
     * Voir un ticket
     */
    public function show(Ticket $ticket)
    {
        // Vérifier que le ticket appartient à l'utilisateur
        if ($ticket->user_id !== auth()->id()) {
            abort(403);
        }

        $ticket->load([
            'replies' => function($query) {
                $query->where('is_internal', false)
                      ->orderBy('created_at', 'asc')
                      ->with('user');
            },
            'attachments',
            'assignedTo'
        ]);

        return view('tickets.show', compact('ticket'));
    }

    /**
     * Répondre à un ticket
     */
    public function reply(Request $request, Ticket $ticket)
    {
        // Vérifier que le ticket appartient à l'utilisateur
        if ($ticket->user_id !== auth()->id()) {
            abort(403);
        }

        // Vérifier si le ticket peut recevoir des réponses
        if (!$ticket->canBeReplied()) {
            return back()->with('error', 'Ce ticket est fermé et ne peut plus recevoir de réponses.');
        }

        $data = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
            'attachments.*' => ['nullable', 'file', 'max:10240'],
        ]);

        $reply = TicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id' => $request->user()->id,
            'message' => $data['message'],
            'is_internal' => false,
        ]);

        // Gérer les fichiers joints
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $filename = uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('tickets/' . $ticket->id, $filename, 'private');

                TicketAttachment::create([
                    'ticket_id' => $ticket->id,
                    'ticket_reply_id' => $reply->id,
                    'user_id' => $request->user()->id,
                    'filename' => $filename,
                    'original_filename' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'path' => $path,
                ]);
            }
        }

        // Si le ticket était résolu, le rouvrir
        if ($ticket->status === 'resolved') {
            $ticket->update(['status' => 'open']);
        }

        return back()->with('success', 'Réponse envoyée avec succès !');
    }

    /**
     * Télécharger une pièce jointe
     */
    public function downloadAttachment(TicketAttachment $attachment)
    {
        // Vérifier que l'utilisateur a accès
        if ($attachment->ticket->user_id !== auth()->id()) {
            abort(403);
        }

        return Storage::disk('private')->download(
            $attachment->path,
            $attachment->original_filename
        );
    }

    /**
     * Fermer un ticket (client peut demander la fermeture)
     */
    public function close(Ticket $ticket)
    {
        if ($ticket->user_id !== auth()->id()) {
            abort(403);
        }

        $ticket->markAsClosed();

        return back()->with('success', 'Ticket fermé avec succès !');
    }

    /**
     * Rouvrir un ticket
     */
    public function reopen(Ticket $ticket)
    {
        if ($ticket->user_id !== auth()->id()) {
            abort(403);
        }

        if (!$ticket->isClosed()) {
            return back()->with('error', 'Ce ticket n\'est pas fermé.');
        }

        $ticket->reopen();

        return back()->with('success', 'Ticket réouvert avec succès !');
    }
}