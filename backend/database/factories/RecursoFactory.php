<?php

namespace Database\Factories;

use App\Models\Recurso;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Recurso>
 */
class RecursoFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $slug = fake()->unique()->slug(3);

        return [
            'slug' => $slug,
            'titulo' => fake()->sentence(4),
            'tipo' => 'infografia',
            'tema' => fake()->randomElement(array_keys(Recurso::TEMAS)),
            'descripcion' => fake()->paragraphs(2, true),
            'autoria' => 'Cátedra Ceuta-IATEB',
            'licencia' => 'cc-by-nc-sa-4.0',
            'imagenes' => ["media/infografias/{$slug}.png"],
            'miniatura' => "media/miniaturas/{$slug}.webp",
            'pdf' => null,
            'orden' => 0,
            'publicado' => true,
        ];
    }

    /** Infografía de varias páginas, con PDF. */
    public function paginas(int $total = 3): static
    {
        return $this->state(fn (array $atributos) => [
            'imagenes' => array_map(fn ($n) => "media/infografias/{$atributos['slug']}-{$n}.jpg", range(1, $total)),
            'pdf' => "media/infografias/{$atributos['slug']}.pdf",
        ]);
    }

    public function oculto(): static
    {
        return $this->state(fn () => ['publicado' => false]);
    }
}
