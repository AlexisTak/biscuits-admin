<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ajouter address et zip_code à la table devis
        Schema::table('devis', function (Blueprint $table) {
            $table->string('address', 500)->nullable()->after('phone');
            $table->string('zip_code', 20)->nullable()->after('address');
        });

        // Vérifier si address et zip_code existent déjà dans contacts
        // (ta migration contacts les a peut-être déjà)
        if (!Schema::hasColumn('contacts', 'address')) {
            Schema::table('contacts', function (Blueprint $table) {
                $table->string('address', 500)->nullable()->after('country');
            });
        }

        if (!Schema::hasColumn('contacts', 'zip_code')) {
            Schema::table('contacts', function (Blueprint $table) {
                $table->string('zip_code', 20)->nullable()->after('address');
            });
        }
    }

    public function down(): void
    {
        Schema::table('devis', function (Blueprint $table) {
            $table->dropColumn(['address', 'zip_code']);
        });

        Schema::table('contacts', function (Blueprint $table) {
            if (Schema::hasColumn('contacts', 'address')) {
                $table->dropColumn('address');
            }
            if (Schema::hasColumn('contacts', 'zip_code')) {
                $table->dropColumn('zip_code');
            }
        });
    }
};