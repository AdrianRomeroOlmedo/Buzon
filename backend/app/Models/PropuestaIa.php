<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Propuesta de la IA para una sugerencia (la escribe la IA con un INSERT).
 */
class PropuestaIa extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'propuestas_ia';

    protected $fillable = [
        'sugerencia_id',
        'categoria_id',
        'subcategoria_1_id',
        'subcategoria_2_id',
        'organismo_id',
        'nivel_urgencia',
        'texto_desidentificado',
        'propuesta_desidentificada',
        'modelo_version',
        'revisada_por',
        'revisada_at',
        'categoria_correcta',
        'subcategorias_correctas',
        'organismo_correcto',
        'urgencia_correcta',
        'texto_editado',
        'datos_personales_omitidos',
        'sobreanonimizada',
        'identificadores_residuales',
        'similitud_texto',
        'privacidad_evaluada_at',
        'acierto_subcategorias',
        'propuesta_aplicada',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'revisada_at' => 'datetime',
            'categoria_correcta' => 'boolean',
            'subcategorias_correctas' => 'boolean',
            'organismo_correcto' => 'boolean',
            'urgencia_correcta' => 'boolean',
            'texto_editado' => 'boolean',
            'datos_personales_omitidos' => 'boolean',
            'sobreanonimizada' => 'boolean',
            'identificadores_residuales' => 'integer',
            'similitud_texto' => 'float',
            'privacidad_evaluada_at' => 'datetime',
            'acierto_subcategorias' => 'float',
            'propuesta_aplicada' => 'boolean',
        ];
    }

    public function sugerencia(): BelongsTo
    {
        return $this->belongsTo(Sugerencia::class);
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    public function subcategoria1(): BelongsTo
    {
        return $this->belongsTo(Subcategoria::class, 'subcategoria_1_id');
    }

    public function subcategoria2(): BelongsTo
    {
        return $this->belongsTo(Subcategoria::class, 'subcategoria_2_id');
    }

    public function organismo(): BelongsTo
    {
        return $this->belongsTo(Organismo::class);
    }

    public function revisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revisada_por');
    }

    /**
     * @return list<int>
     */
    public function subcategoriaIds(): array
    {
        return array_values(array_map('intval', array_filter([$this->subcategoria_1_id, $this->subcategoria_2_id])));
    }

    /**
     * Nombres de las subcategorías propuestas, en orden.
     *
     * @return list<string>
     */
    public function nombresSubcategorias(): array
    {
        return array_values(array_filter([$this->subcategoria1?->nombre, $this->subcategoria2?->nombre]));
    }
}
