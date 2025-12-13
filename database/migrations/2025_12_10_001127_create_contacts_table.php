<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            
            // Informations de contact
            $table->string('name', 100);
            $table->string('email', 255)->index();
            $table->string('phone', 20)->nullable();
            $table->string('country', 100);
            $table->string('service', 255);
            
            // ✅ Adresse complète
            $table->string('address', 500)->nullable();
            $table->string('zip_code', 20)->nullable();
            
            $table->text('message');
            
            // Métadonnées
            $table->string('status')->default('pending')->index();
            $table->text('notes')->nullable();
            $table->string('priority')->default('normal')->nullable();
            $table->boolean('is_read')->default(false)->index();
            
            // Tracking
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            
            // ✅ Fingerprint (pour détection de doublons)
            $table->string('fingerprint', 64)->nullable()->index();
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Index pour performances
            $table->index('created_at');
            $table->index(['status', 'created_at']);
            $table->index(['is_read', 'status']);
            $table->index(['email', 'created_at']);
        });

        // Contraintes CHECK pour PostgreSQL
        DB::statement("
            ALTER TABLE contacts 
            ADD CONSTRAINT contacts_status_check 
            CHECK (status IN ('pending', 'processed', 'archived'))
        ");
        
        DB::statement("
            ALTER TABLE contacts 
            ADD CONSTRAINT contacts_priority_check 
            CHECK (priority IN ('low', 'normal', 'high'))
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};