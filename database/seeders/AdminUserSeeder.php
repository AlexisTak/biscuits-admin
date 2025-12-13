<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Créer les utilisateurs administrateurs par défaut
     */
    public function run(): void
    {
        $this->command->info('🔄 Création des utilisateurs administrateurs...');

        // ============================================
        // SUPER ADMIN
        // ============================================
        $superAdmin = User::updateOrCreate(
            ['email' => 'admin@biscuits.dev'],
            [
                'name' => 'Admin Biscuits',
                'password' => Hash::make('password'), // ⚠️ À changer en production !
                'role' => 'super_admin',
                'email_verified_at' => now(),
            ]
        );

        $this->command->info($superAdmin->wasRecentlyCreated 
            ? '✅ Super Admin créé' 
            : '♻️  Super Admin mis à jour'
        );

        // ============================================
        // ADMIN SUPPORT
        // ============================================
        $supportAdmin = User::updateOrCreate(
            ['email' => 'support@biscuits.dev'],
            [
                'name' => 'Support Biscuits',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        $this->command->info($supportAdmin->wasRecentlyCreated 
            ? '✅ Admin Support créé' 
            : '♻️  Admin Support mis à jour'
        );

        // ============================================
        // UTILISATEUR TEST (Développement uniquement)
        // ============================================
        if (app()->environment('local')) {
            $testUser = User::updateOrCreate(
                ['email' => 'test@example.com'],
                [
                    'name' => 'Test User',
                    'password' => Hash::make('password'),
                    'role' => 'user',
                    'email_verified_at' => now(),
                ]
            );

            $this->command->info($testUser->wasRecentlyCreated 
                ? '✅ Utilisateur Test créé' 
                : '♻️  Utilisateur Test mis à jour'
            );
        }

        // ============================================
        // RÉSUMÉ
        // ============================================
        $this->command->newLine();
        $this->command->info('════════════════════════════════════════');
        $this->command->info('✅ Seeders admin terminés avec succès !');
        $this->command->info('════════════════════════════════════════');
        $this->command->table(
            ['Email', 'Rôle', 'Mot de passe'],
            [
                ['admin@biscuits.dev', 'super_admin', 'password'],
                ['support@biscuits.dev', 'admin', 'password'],
                app()->environment('local') ? ['test@example.com', 'user', 'password'] : null,
            ]
        );
        $this->command->newLine();
        $this->command->warn('⚠️  IMPORTANT : Changez ces mots de passe en production !');
        $this->command->newLine();
    }
}