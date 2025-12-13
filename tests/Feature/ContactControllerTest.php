<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ContactControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_contact_via_api()
    {
        $response = $this->postJson('/api/contacts', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'country' => 'France',
            'service' => 'Test Service',
            'message' => 'This is a test message',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['id'],
            ]);

        $this->assertDatabaseHas('contacts', [
            'email' => 'john@example.com',
        ]);
    }

    public function test_honeypot_blocks_spam()
    {
        $response = $this->postJson('/api/contacts', [
            'name' => 'Spammer',
            'email' => 'spam@example.com',
            'country' => 'France',
            'service' => 'Test',
            'message' => 'Spam message',
            'honey' => 'filled', // ⚠️ Honeypot rempli
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseMissing('contacts', [
            'email' => 'spam@example.com',
        ]);
    }

    public function test_admin_can_update_contact()
    {
        $admin = User::factory()->create();
        $contact = Contact::factory()->create();

        $response = $this->actingAs($admin)
            ->patch(route('admin.contacts.update', $contact), [
                'name' => 'Updated Name',
                'email' => $contact->email,
                'country' => $contact->country,
                'service' => $contact->service,
                'message' => $contact->message,
                'status' => 'processed',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'name' => 'Updated Name',
        ]);
    }
}