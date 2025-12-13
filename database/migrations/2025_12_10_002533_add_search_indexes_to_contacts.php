<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            // Vérifie si un index existe avant de le créer
            if (!$this->indexExists('contacts', 'contacts_name_email_index')) {
                $table->index(['name', 'email'], 'contacts_name_email_index');
            }

            if (!$this->indexExists('contacts', 'contacts_phone_index')) {
                $table->index('phone', 'contacts_phone_index');
            }

            if (!$this->indexExists('contacts', 'contacts_status_created_at_index')) {
                $table->index(['status', 'created_at'], 'contacts_status_created_at_index');
            }
        });

        // Full-text search index
        if (!$this->pgIndexExists('contacts_search_idx')) {
            DB::statement("
                CREATE INDEX contacts_search_idx 
                ON contacts USING gin (to_tsvector('french', name || ' ' || email || ' ' || message))
            ");
        }
    }

    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropIndex('contacts_name_email_index');
            $table->dropIndex('contacts_phone_index');
            $table->dropIndex('contacts_status_created_at_index');
        });

        DB::statement('DROP INDEX IF EXISTS contacts_search_idx');
    }

    /**
     * Vérifie si un index existe (Laravel + PostgreSQL)
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $result = DB::select("
            SELECT 1 
            FROM pg_indexes 
            WHERE tablename = ? AND indexname = ?
        ", [$table, $indexName]);

        return !empty($result);
    }

    /**
     * Vérifie un index PostgreSQL spécifique
     */
    private function pgIndexExists(string $indexName): bool
    {
        $result = DB::select("
            SELECT 1 
            FROM pg_indexes 
            WHERE indexname = ?
        ", [$indexName]);

        return !empty($result);
    }
};
