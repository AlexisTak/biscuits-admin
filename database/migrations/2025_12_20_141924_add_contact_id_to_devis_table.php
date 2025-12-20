<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('devis', function (Blueprint $table) {
            // Ajouter la colonne contact_id avec contrainte de clé étrangère
            $table->foreignId('contact_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('contacts')
                  ->onDelete('set null'); // Ou cascade selon votre besoin
            
            // Index pour optimiser les requêtes
            $table->index('contact_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('devis', function (Blueprint $table) {
            $table->dropForeign(['contact_id']);
            $table->dropColumn('contact_id');
        });
    }
};