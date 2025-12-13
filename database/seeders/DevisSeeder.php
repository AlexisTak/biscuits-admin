<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Devis;

class DevisSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('💰 Création des devis de test...');

        // Vérifier si des devis existent déjà
        if (Devis::count() > 0) {
            $this->command->warn('⚠️  Des devis existent déjà. Seeding ignoré.');
            return;
        }

        // 15 devis en attente
        Devis::factory(15)->pending()->create();
        $this->command->info('  ✅ 15 devis en attente créés');

        // 10 devis approuvés
        Devis::factory(10)->approved()->create();
        $this->command->info('  ✅ 10 devis approuvés créés');

        // 5 devis refusés
        Devis::factory(5)->state(['status' => 'rejected'])->create();
        $this->command->info('  ✅ 5 devis refusés créés');

        $this->command->info('✅ Total : ' . Devis::count() . ' devis créés');
    }
}