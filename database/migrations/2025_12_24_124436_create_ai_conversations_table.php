<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('assistant'); // 'support', 'dev', 'sales'
            $table->json('meta')->nullable();
            $table->timestamps();
            
            // Index
            $table->index(['user_id', 'created_at']);
            $table->index('assistant');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_conversations');
    }
};