<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Contact;

class ContactSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('📧 Création des contacts de test...');

        // Vérifier si des contacts existent déjà
        if (Contact::count() > 0) {
            $this->command->warn('⚠️  Des contacts existent déjà. Seeding ignoré.');
            return;
        }

        // 20 contacts en attente (non lus)
        Contact::factory(20)->pending()->create();
        $this->command->info('  ✅ 20 contacts en attente créés');

        // 15 contacts traités (lus)
        Contact::factory(15)->processed()->create();
        $this->command->info('  ✅ 15 contacts traités créés');

        // 10 contacts archivés
        Contact::factory(10)->state([
            'status' => 'archived',
            'is_read' => true,
        ])->create();
        $this->command->info('  ✅ 10 contacts archivés créés');

        $this->command->info('✅ Total : ' . Contact::count() . ' contacts créés');
    }
}