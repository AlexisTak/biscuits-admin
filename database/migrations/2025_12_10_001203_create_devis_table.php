<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('devis', function (Blueprint $table) {
            $table->id();
            
            // Informations client
            $table->string('name', 100);
            $table->string('email', 255)->index();
            $table->string('phone', 20)->nullable();
            
            // Détails du devis
            $table->string('service', 255);
            $table->string('budget', 100)->nullable();
            $table->text('message')->nullable();
            $table->decimal('amount', 10, 2)->nullable()->index();
            
            // Métadonnées
            $table->string('status')->default('pending')->index();
            $table->text('notes')->nullable();
            $table->string('priority')->default('normal')->nullable();
            
            // Tracking
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Index
            $table->index('created_at');
            $table->index(['status', 'created_at']);
            $table->index(['email', 'created_at']);
        });

        // Contraintes CHECK pour PostgreSQL
        DB::statement("
            ALTER TABLE devis 
            ADD CONSTRAINT devis_status_check 
            CHECK (status IN ('pending', 'approved', 'rejected', 'processed'))
        ");
        
        DB::statement("
            ALTER TABLE devis 
            ADD CONSTRAINT devis_priority_check 
            CHECK (priority IN ('low', 'normal', 'high'))
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('devis');
    }
};