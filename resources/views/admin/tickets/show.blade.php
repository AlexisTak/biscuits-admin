@extends('layouts.app')

@section('title', 'Ticket #' . $ticket->ticket_number)

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center gap-3 mb-4">
            <a href="{{ route('tickets.index') }}" 
               class="inline-flex items-center text-gray-400 hover:text-white transition-colors">
                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Retour
            </a>
        </div>
        
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <h1 class="text-3xl font-bold text-white">
                        Ticket {{ $ticket->ticket_number }}
                    </h1>
                    <span class="px-3 py-1 text-sm font-medium rounded-full
                        {{ $ticket->status === 'open' ? 'bg-green-500/20 text-green-300 border border-green-500/30' : '' }}
                        {{ $ticket->status === 'in_progress' ? 'bg-blue-500/20 text-blue-300 border border-blue-500/30' : '' }}
                        {{ $ticket->status === 'waiting' ? 'bg-yellow-500/20 text-yellow-300 border border-yellow-500/30' : '' }}
                        {{ $ticket->status === 'resolved' ? 'bg-purple-500/20 text-purple-300 border border-purple-500/30' : '' }}
                        {{ $ticket->status === 'closed' ? 'bg-gray-500/20 text-gray-300 border border-gray-500/30' : '' }}">
                        {{ $ticket->status_label }}
                    </span>
                </div>
                <p class="text-gray-400">
                    Créé le {{ $ticket->created_at->format('d/m/Y à H:i') }}
                </p>
            </div>
            
            <!-- Actions -->
            @if(!$ticket->isClosed())
            <form method="POST" action="{{ route('tickets.close', $ticket) }}" class="inline">
                @csrf
                <button type="submit" 
                        onclick="return confirm('Voulez-vous vraiment fermer ce ticket ?')"
                        class="inline-flex items-center px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition-colors border border-gray-600">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Fermer le ticket
                </button>
            </form>
            @else
            <form method="POST" action="{{ route('tickets.reopen', $ticket) }}" class="inline">
                @csrf
                <button type="submit" 
                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-lg transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Rouvrir
                </button>
            </form>
            @endif
        </div>
    </div>

    <!-- Infos du ticket -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        <div class="bg-gray-800/50 backdrop-blur-sm border border-gray-700 rounded-xl p-4">
            <p class="text-sm text-gray-400 mb-1">Priorité</p>
            <span class="inline-flex items-center px-3 py-1 text-sm font-medium rounded-full
                {{ $ticket->priority === 'low' ? 'bg-gray-500/20 text-gray-300' : '' }}
                {{ $ticket->priority === 'medium' ? 'bg-blue-500/20 text-blue-300' : '' }}
                {{ $ticket->priority === 'high' ? 'bg-orange-500/20 text-orange-300' : '' }}
                {{ $ticket->priority === 'urgent' ? 'bg-red-500/20 text-red-300' : '' }}">
                {{ $ticket->priority_label }}
            </span>
        </div>
        
        <div class="bg-gray-800/50 backdrop-blur-sm border border-gray-700 rounded-xl p-4">
            <p class="text-sm text-gray-400 mb-1">Catégorie</p>
            <p class="text-white font-medium">{{ $ticket->category_label }}</p>
        </div>
        
        <div class="bg-gray-800/50 backdrop-blur-sm border border-gray-700 rounded-xl p-4">
            <p class="text-sm text-gray-400 mb-1">Assigné à</p>
            <p class="text-white font-medium">
                @if($ticket->assignedTo)
                    {{ $ticket->assignedTo->name }}
                @else
                    <span class="text-gray-500">Non assigné</span>
                @endif
            </p>
        </div>
    </div>

    <!-- Description initiale -->
    <div class="bg-gray-800/50 backdrop-blur-sm border border-gray-700 rounded-xl shadow-lg mb-8">
        <div class="p-6 border-b border-gray-700">
            <h2 class="text-xl font-semibold text-white">{{ $ticket->subject }}</h2>
        </div>
        <div class="p-6">
            <div class="flex gap-4">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 rounded-full bg-blue-500/20 border border-blue-500/30 flex items-center justify-center text-lg">
                        👤
                    </div>
                </div>
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="font-medium text-white">{{ $ticket->user->name }}</span>
                        <span class="text-xs text-gray-500">{{ $ticket->created_at->diffForHumans() }}</span>
                    </div>
                    <div class="prose prose-invert max-w-none">
                        <p class="text-gray-300 whitespace-pre-wrap">{{ $ticket->description }}</p>
                    </div>
                    
                    <!-- Pièces jointes initiales -->
                    @if($ticket->attachments->where('ticket_reply_id', null)->count() > 0)
                    <div class="mt-4 space-y-2">
                        <p class="text-sm text-gray-400 mb-2">📎 Pièces jointes :</p>
                        @foreach($ticket->attachments->where('ticket_reply_id', null) as $attachment)
                        <a href="{{ route('tickets.attachment.download', $attachment) }}" 
                           class="inline-flex items-center gap-2 px-3 py-2 bg-gray-900/50 border border-gray-600 rounded-lg text-sm text-gray-300 hover:bg-gray-700/50 transition-colors">
                            <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span>{{ $attachment->original_filename }}</span>
                            <span class="text-xs text-gray-500">({{ $attachment->getFormattedSize() }})</span>
                        </a>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Réponses -->
    @if($ticket->replies->count() > 0)
    <div class="space-y-6 mb-8">
        <h3 class="text-xl font-semibold text-white">💬 Réponses ({{ $ticket->replies->count() }})</h3>
        
        @foreach($ticket->replies as $reply)
        <div class="bg-gray-800/50 backdrop-blur-sm border border-gray-700 rounded-xl shadow-lg p-6">
            <div class="flex gap-4">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 rounded-full {{ $reply->isFromCustomer() ? 'bg-blue-500/20 border border-blue-500/30' : 'bg-purple-500/20 border border-purple-500/30' }} flex items-center justify-center text-lg">
                        {{ $reply->isFromCustomer() ? '👤' : '🛠️' }}
                    </div>
                </div>
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="font-medium text-white">{{ $reply->user->name }}</span>
                        @if(!$reply->isFromCustomer())
                        <span class="px-2 py-0.5 text-xs font-medium bg-purple-500/20 text-purple-300 border border-purple-500/30 rounded">
                            Support
                        </span>
                        @endif
                        <span class="text-xs text-gray-500">{{ $reply->created_at->diffForHumans() }}</span>
                    </div>
                    <div class="prose prose-invert max-w-none">
                        <p class="text-gray-300 whitespace-pre-wrap">{{ $reply->message }}</p>
                    </div>
                    
                    <!-- Pièces jointes de la réponse -->
                    @if($reply->attachments->count() > 0)
                    <div class="mt-4 space-y-2">
                        <p class="text-sm text-gray-400 mb-2">📎 Pièces jointes :</p>
                        @foreach($reply->attachments as $attachment)
                        <a href="{{ route('tickets.attachment.download', $attachment) }}" 
                           class="inline-flex items-center gap-2 px-3 py-2 bg-gray-900/50 border border-gray-600 rounded-lg text-sm text-gray-300 hover:bg-gray-700/50 transition-colors">
                            <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span>{{ $attachment->original_filename }}</span>
                            <span class="text-xs text-gray-500">({{ $attachment->getFormattedSize() }})</span>
                        </a>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    <!-- Formulaire de réponse -->
    @if($ticket->canBeReplied())
    <div class="bg-gray-800/50 backdrop-blur-sm border border-gray-700 rounded-xl shadow-lg p-6">
        <h3 class="text-lg font-semibold text-white mb-4">✍️ Ajouter une réponse</h3>
        
        <form method="POST" action="{{ route('tickets.reply', $ticket) }}" enctype="multipart/form-data">
            @csrf
            
            <div class="mb-4">
                <textarea name="message" 
                          rows="5" 
                          required
                          maxlength="5000"
                          placeholder="Écrivez votre réponse..."
                          class="w-full px-4 py-3 bg-gray-900/50 border border-gray-600 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all resize-none @error('message') border-red-500 @enderror"></textarea>
                @error('message')
                    <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-300 mb-2">Ajouter des fichiers</label>
                <input type="file" 
                       name="attachments[]" 
                       multiple
                       accept="image/*,.pdf,.doc,.docx,.txt,.zip"
                       class="w-full px-4 py-2 bg-gray-900/50 border border-gray-600 rounded-lg text-white file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-500">
            </div>
            
            <div class="flex justify-end">
                <button type="submit" 
                        class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-500 text-white rounded-lg transition-colors shadow-lg shadow-blue-500/20">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                    Envoyer la réponse
                </button>
            </div>
        </form>
    </div>
    @else
    <div class="bg-gray-800/50 backdrop-blur-sm border border-gray-700 rounded-xl shadow-lg p-8 text-center">
        <div class="text-5xl mb-3">🔒</div>
        <p class="text-gray-400">Ce ticket est fermé et ne peut plus recevoir de réponses.</p>
        @if($ticket->isClosed())
        <form method="POST" action="{{ route('tickets.reopen', $ticket) }}" class="inline-block mt-4">
            @csrf
            <button type="submit" 
                    class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-500 text-white rounded-lg transition-colors">
                Rouvrir le ticket
            </button>
        </form>
        @endif
    </div>
    @endif

</div>
@endsection