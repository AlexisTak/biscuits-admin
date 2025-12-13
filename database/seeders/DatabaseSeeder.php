<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🌱 Début du seeding de la base de données...');
        $this->command->newLine();

        // ============================================
        // 1. UTILISATEURS (TOUJOURS EN PREMIER)
        // ============================================
        $this->command->info('👥 Création des utilisateurs...');
        
        // Admin principal
        \App\Models\User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'is_admin' => true,
            'password' => bcrypt('password'),
        ]);

        // Autres admins/staff
        $this->call([
            AdminUserSeeder::class,
        ]);

        // Créer des clients (uniquement en dev)
        if (app()->environment('local')) {
            $this->command->info('👤 Création de clients de test...');
            \App\Models\User::factory(10)->create([
                'is_admin' => false,
            ]);
        }

        // ============================================
        // 2. CONTACTS & DEVIS (Uniquement en développement)
        // ============================================
        if (app()->environment('local')) {
            $this->call([
                ContactSeeder::class,
                DevisSeeder::class,
            ]);
        }

        // ============================================
        // 3. TICKETS (Uniquement en développement)
        // ============================================
        if (app()->environment('local')) {
            $this->command->newLine();
            $this->call([
                TicketSeeder::class,
            ]);
        }

        $this->command->newLine();
        $this->command->info('✅ Seeding terminé avec succès !');
        $this->command->newLine();
        
        // Afficher les infos de connexion
        $this->command->info('🔑 Connexion Admin :');
        $this->command->info('   Email: admin@example.com');
        $this->command->info('   Password: password');
        $this->command->newLine();
    }
}