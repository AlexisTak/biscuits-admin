@extends('layouts.app')

@section('title', 'Créer un ticket')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
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
        <h1 class="text-3xl font-bold text-white">
            ✍️ Créer un ticket de support
        </h1>
        <p class="mt-2 text-gray-400">
            Décrivez votre problème ou votre demande en détail
        </p>
    </div>

    <!-- Formulaire -->
    <div class="bg-gray-800/50 backdrop-blur-sm border border-gray-700 rounded-xl shadow-lg p-8">
        <form method="POST" action="{{ route('tickets.store') }}" enctype="multipart/form-data">
            @csrf

            <!-- Sujet -->
            <div class="mb-6">
                <label for="subject" class="block text-sm font-medium text-gray-300 mb-2">
                    Sujet <span class="text-red-400">*</span>
                </label>
                <input type="text" 
                       id="subject" 
                       name="subject" 
                       value="{{ old('subject') }}"
                       required
                       maxlength="255"
                       placeholder="Ex: Problème de connexion à mon compte"
                       class="w-full px-4 py-3 bg-gray-900/50 border border-gray-600 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all @error('subject') border-red-500 @enderror">
                @error('subject')
                    <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Catégorie et Priorité -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Catégorie -->
                <div>
                    <label for="category" class="block text-sm font-medium text-gray-300 mb-2">
                        Catégorie <span class="text-red-400">*</span>
                    </label>
                    <select id="category" 
                            name="category" 
                            required
                            class="w-full px-4 py-3 bg-gray-900/50 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all @error('category') border-red-500 @enderror">
                        <option value="">Sélectionnez...</option>
                        <option value="technical" {{ old('category') === 'technical' ? 'selected' : '' }}>🔧 Technique</option>
                        <option value="billing" {{ old('category') === 'billing' ? 'selected' : '' }}>💰 Facturation</option>
                        <option value="feature_request" {{ old('category') === 'feature_request' ? 'selected' : '' }}>💡 Demande de fonctionnalité</option>
                        <option value="bug" {{ old('category') === 'bug' ? 'selected' : '' }}>🐛 Bug</option>
                        <option value="other" {{ old('category') === 'other' ? 'selected' : '' }}>📋 Autre</option>
                    </select>
                    @error('category')
                        <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Priorité -->
                <div>
                    <label for="priority" class="block text-sm font-medium text-gray-300 mb-2">
                        Priorité <span class="text-red-400">*</span>
                    </label>
                    <select id="priority" 
                            name="priority" 
                            required
                            class="w-full px-4 py-3 bg-gray-900/50 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all @error('priority') border-red-500 @enderror">
                        <option value="medium" {{ old('priority', 'medium') === 'medium' ? 'selected' : '' }}>📘 Moyenne</option>
                        <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>📗 Faible</option>
                        <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>📙 Haute</option>
                        <option value="urgent" {{ old('priority') === 'urgent' ? 'selected' : '' }}>📕 Urgente</option>
                    </select>
                    @error('priority')
                        <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Description -->
            <div class="mb-6">
                <label for="description" class="block text-sm font-medium text-gray-300 mb-2">
                    Description détaillée <span class="text-red-400">*</span>
                </label>
                <textarea id="description" 
                          name="description" 
                          rows="8" 
                          required
                          maxlength="5000"
                          placeholder="Décrivez votre problème en détail. Plus vous donnez d'informations, plus nous pourrons vous aider rapidement."
                          class="w-full px-4 py-3 bg-gray-900/50 border border-gray-600 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all resize-none @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                <div class="mt-2 flex items-center justify-between text-xs">
                    @error('description')
                        <p class="text-red-400">{{ $message }}</p>
                    @else
                        <p class="text-gray-500">Maximum 5000 caractères</p>
                    @enderror
                    <p class="text-gray-500" id="charCount">0 / 5000</p>
                </div>
            </div>

            <!-- Pièces jointes -->
            <div class="mb-8">
                <label class="block text-sm font-medium text-gray-300 mb-2">
                    Pièces jointes
                </label>
                <div class="border-2 border-dashed border-gray-600 rounded-lg p-6 text-center hover:border-blue-500 transition-colors">
                    <input type="file" 
                           id="attachments" 
                           name="attachments[]" 
                           multiple
                           accept="image/*,.pdf,.doc,.docx,.txt,.zip"
                           class="hidden"
                           onchange="updateFileList(this)">
                    <label for="attachments" class="cursor-pointer">
                        <svg class="w-12 h-12 mx-auto mb-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                        <p class="text-gray-400 mb-1">Cliquez pour ajouter des fichiers</p>
                        <p class="text-xs text-gray-500">Images, PDF, documents (max 10MB par fichier)</p>
                    </label>
                </div>
                <div id="fileList" class="mt-3 space-y-2"></div>
                @error('attachments.*')
                    <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-between pt-6 border-t border-gray-700">
                <a href="{{ route('tickets.index') }}" 
                   class="px-6 py-3 text-gray-400 hover:text-white transition-colors">
                    Annuler
                </a>
                <button type="submit" 
                        class="inline-flex items-center px-8 py-3 bg-blue-600 hover:bg-blue-500 text-white rounded-lg transition-colors shadow-lg shadow-blue-500/20">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Créer le ticket
                </button>
            </div>
        </form>
    </div>

</div>

<script>
// Compteur de caractères
const description = document.getElementById('description');
const charCount = document.getElementById('charCount');

if (description && charCount) {
    description.addEventListener('input', function() {
        const count = this.value.length;
        charCount.textContent = `${count} / 5000`;
        
        if (count > 4500) {
            charCount.classList.add('text-red-400');
        } else {
            charCount.classList.remove('text-red-400');
        }
    });
    
    // Init
    if (description.value) {
        charCount.textContent = `${description.value.length} / 5000`;
    }
}

// Liste des fichiers
function updateFileList(input) {
    const fileList = document.getElementById('fileList');
    fileList.innerHTML = '';
    
    if (input.files.length > 0) {
        Array.from(input.files).forEach((file, index) => {
            const fileSize = (file.size / 1024 / 1024).toFixed(2);
            const div = document.createElement('div');
            div.className = 'flex items-center justify-between p-3 bg-gray-900/50 border border-gray-600 rounded-lg';
            div.innerHTML = `
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <div>
                        <p class="text-sm text-white">${file.name}</p>
                        <p class="text-xs text-gray-500">${fileSize} MB</p>
                    </div>
                </div>
                <button type="button" onclick="removeFile(${index})" class="text-red-400 hover:text-red-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            `;
            fileList.appendChild(div);
        });
    }
}

function removeFile(index) {
    const input = document.getElementById('attachments');
    const dt = new DataTransfer();
    
    Array.from(input.files).forEach((file, i) => {
        if (i !== index) {
            dt.items.add(file);
        }
    });
    
    input.files = dt.files;
    updateFileList(input);
}
</script>
@endsection