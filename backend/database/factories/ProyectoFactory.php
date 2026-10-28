<?php

namespace Database\Factories;

use App\Models\Proyecto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Proyecto>
 */
class ProyectoFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'titulo' => fake()->unique()->sentence(3),
            'resumen' => fake()->sentence(12),
            'descripcion' => fake()->paragraphs(2, true),
            'linea' => fake()->randomElement(array_keys(Proyecto::LINEAS)),
            'estado' => 'en-marcha',
            'fecha_inicio' => fake()->dateTimeBetween('-2 years', 'now'),
            'destacado' => false,
            'publicado' => true,
        ];
    }

    public function destacado(): static
    {
        return $this->state(fn () => ['destacado' => true]);
    }

    public function oculto(): static
    {
        return $this->state(fn () => ['publicado' => false]);
    }
}
