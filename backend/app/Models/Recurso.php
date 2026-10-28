<?php

namespace App\Models;

use Database\Factories\RecursoFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Recurso divulgativo de la cátedra (de momento, infografías) que se muestra en /recursos.
 *
 * - El slug se genera del título solo al crear: si luego cambia el título, la URL se mantiene.
 * - La descripción es la alternativa accesible a la imagen: debe recoger lo esencial de su contenido.
 */
class Recurso extends Model
{
    /** @use HasFactory<RecursoFactory> */
    use HasFactory;

    protected $table = 'recursos';

    public const TIPOS = [
        'infografia' => 'Infografía',
    ];

    public const TEMAS = [
        'educacion' => 'Educación',
        'emprendimiento' => 'Emprendimiento',
        'investigacion' => 'Investigación',
        'ciberseguridad' => 'Ciberseguridad',
    ];

    public const LICENCIAS = [
        'cc-by-nc-sa-4.0' => ['etiqueta' => 'CC BY-NC-SA 4.0', 'url' => 'https://creativecommons.org/licenses/by-nc-sa/4.0/deed.es'],
        'cc-by-4.0' => ['etiqueta' => 'CC BY 4.0', 'url' => 'https://creativecommons.org/licenses/by/4.0/deed.es'],
    ];

    protected $fillable = [
        'slug', 'titulo', 'tipo', 'tema', 'descripcion', 'autoria', 'licencia',
        'imagenes', 'miniatura', 'pdf', 'orden', 'publicado',
    ];

    protected function casts(): array
    {
        return [
            'imagenes' => 'array',
            'orden' => 'integer',
            'publicado' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Recurso $recurso) {
            $recurso->slug ??= Str::slug($recurso->titulo);
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** Solo los visibles al público, en el orden indicado (más reciente primero dentro del mismo orden). */
    public function scopePublicados(Builder $query): void
    {
        $query->where('publicado', true)
            ->orderBy('orden')
            ->orderByDesc('id');
    }

    public function tipoEtiqueta(): string
    {
        return self::TIPOS[$this->tipo] ?? $this->tipo;
    }

    public function temaEtiqueta(): string
    {
        return self::TEMAS[$this->tema] ?? $this->tema;
    }

    /** @return array{etiqueta: string, url: string}|null */
    public function licenciaDatos(): ?array
    {
        return self::LICENCIAS[$this->licencia] ?? null;
    }

    /** Formato de un fichero de imagen para el botón de descarga ("PNG", "WEBP"...). */
    public static function formato(string $ruta): string
    {
        return strtoupper(pathinfo($ruta, PATHINFO_EXTENSION));
    }

    public function tieneVariasPaginas(): bool
    {
        return count($this->imagenes) > 1;
    }

    /** Markdown a HTML sin permitir HTML incrustado ni enlaces javascript:. */
    public function descripcionHtml(): string
    {
        return Str::markdown($this->descripcion, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
    }

    /** Primer párrafo en texto plano, para meta description y texto alternativo corto. */
    public function resumen(): string
    {
        return Str::limit(trim(strip_tags(Str::before($this->descripcionHtml(), '</p>'))), 200);
    }
}
