<?php

namespace App\Models;

use Database\Factories\ProyectoFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Proyecto de la cátedra que se muestra en /proyectos y en la landing.
 *
 * - El slug se genera del título solo al crear: si luego cambia el título, la URL se mantiene.
 * - La descripción se escribe en Markdown y se convierte quitando cualquier HTML (ver descripcionHtml).
 */
class Proyecto extends Model
{
    /** @use HasFactory<ProyectoFactory> */
    use HasFactory;

    protected $table = 'proyectos';

    /** Líneas de trabajo de la cátedra: clave => [etiqueta, color de la tarjeta sin imagen]. */
    public const LINEAS = [
        'educacion' => ['etiqueta' => 'Educación', 'color' => '#2B509E'],
        'salud' => ['etiqueta' => 'Salud', 'color' => '#0F766E'],
        'servicios-sociales' => ['etiqueta' => 'Servicios Sociales', 'color' => '#7C3AED'],
        'economia' => ['etiqueta' => 'Economía', 'color' => '#D6551A'],
    ];

    public const ESTADOS = [
        'en-marcha' => 'En marcha',
        'finalizado' => 'Finalizado',
    ];

    protected $fillable = [
        'slug', 'titulo', 'resumen', 'descripcion', 'imagen', 'linea', 'estado',
        'fecha_inicio', 'fecha_fin', 'socios', 'enlace_url', 'enlace_texto', 'destacado', 'publicado',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
            'destacado' => 'boolean',
            'publicado' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Proyecto $proyecto) {
            $proyecto->slug ??= Str::slug($proyecto->titulo);
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** Solo los visibles al público, destacados primero y luego los más recientes. */
    public function scopePublicados(Builder $query): void
    {
        $query->where('publicado', true)
            ->orderByDesc('destacado')
            ->orderByDesc('fecha_inicio')
            ->orderByDesc('id');
    }

    public function lineaEtiqueta(): string
    {
        return self::LINEAS[$this->linea]['etiqueta'] ?? $this->linea;
    }

    public function lineaColor(): string
    {
        return self::LINEAS[$this->linea]['color'] ?? '#2B509E';
    }

    public function estadoEtiqueta(): string
    {
        return self::ESTADOS[$this->estado] ?? $this->estado;
    }

    /** "2026 –" si sigue en marcha, "2025 – 2026" si terminó, null si no hay fechas. */
    public function periodo(): ?string
    {
        if (! $this->fecha_inicio) {
            return null;
        }

        $fin = $this->fecha_fin?->year;

        if ($fin === $this->fecha_inicio->year) {
            return (string) $fin;
        }

        return $this->fecha_inicio->year.' – '.($fin ?? '');
    }

    /** Markdown a HTML sin permitir HTML incrustado ni enlaces javascript:. */
    public function descripcionHtml(): string
    {
        return Str::markdown($this->descripcion, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
    }
}
