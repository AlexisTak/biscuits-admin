<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Contact;

class ContactFactory extends Factory
{
    protected $model = Contact::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'country' => $this->faker->randomElement([
                'France', 'Belgique', 'Suisse', 'Canada', 
                'Luxembourg', 'Allemagne', 'Espagne', 'Italie'
            ]),
            'service' => $this->faker->randomElement([
                'StarterKit Next.js Pro',
                'StarterKit SaaS',
                'Chatbot IA Personnalisé',
                'Agent IA Autonome',
                'Automatisation IA',
                'Consulting & Coaching',
            ]),
            'address' => $this->faker->streetAddress(),
            'zip_code' => $this->faker->postcode(),
            'message' => $this->faker->paragraphs(3, true),
            'status' => $this->faker->randomElement(['pending', 'processed', 'archived']),
            'priority' => $this->faker->randomElement(['low', 'normal', 'high']),
            'is_read' => $this->faker->boolean(30),
            'notes' => $this->faker->optional()->paragraph(),
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'created_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
        ];
    }

    /**
     * Contact non lu en attente
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'is_read' => false,
        ]);
    }

    /**
     * Contact traité et lu
     */
    public function processed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'processed',
            'is_read' => true,
        ]);
    }
}