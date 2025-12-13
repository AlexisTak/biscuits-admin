<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Devis;

class DevisFactory extends Factory
{
    protected $model = Devis::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'service' => $this->faker->randomElement([
                'StarterKit Next.js Pro',
                'StarterKit SaaS',
                'Chatbot IA Personnalisé',
                'Agent IA Autonome',
            ]),
            'budget' => $this->faker->randomElement([
                '< 5 000 €',
                '5 000 € - 10 000 €',
                '10 000 € - 25 000 €',
                '> 25 000 €',
            ]),
            'amount' => $this->faker->randomFloat(2, 1000, 50000),
            'message' => $this->faker->optional()->paragraph(),
            'status' => $this->faker->randomElement(['pending', 'approved', 'rejected', 'processed']),
            'priority' => $this->faker->randomElement(['low', 'normal', 'high']),
            'notes' => $this->faker->optional()->paragraph(),
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'created_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
        ];
    }

    /**
     * Devis approuvé
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
        ]);
    }

    /**
     * Devis en attente
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }
}