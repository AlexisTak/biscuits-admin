<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('user')->after('password')->index();
        });

        // Contrainte CHECK pour PostgreSQL
        DB::statement("
            ALTER TABLE users 
            ADD CONSTRAINT users_role_check 
            CHECK (role IN ('user', 'admin', 'super_admin'))
        ");

        // Mettre à jour les utilisateurs existants (si nécessaire)
        DB::table('users')->update(['role' => 'user']);
    }

    public function down(): void
    {
        // Supprimer la contrainte avant de supprimer la colonne
        DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check");
        
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};