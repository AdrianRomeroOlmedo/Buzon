<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Métricas más finas para evaluar la IA (panel «Eficacia de la IA»).
 *
 * - Anonimización: «texto_editado» no distingue un retoque de estilo de un dato
 *   personal que se le escapó a la IA, que es el error grave. Ahora la persona
 *   revisora lo indica y, además, se cuentan los identificadores que el detector
 *   automático encuentra en el texto de la IA.
 * - Subcategorías: acierto parcial (acertar 1 de 2 no es un fallo total).
 * - Sesgo de automatización: si se usó «Usar propuesta de la IA» antes de
 *   confirmar, el acierto puede estar inflado y se separa en el panel.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('propuestas_ia', function (Blueprint $table) {
            $table->boolean('datos_personales_omitidos')->nullable()->after('texto_editado');
            $table->boolean('sobreanonimizada')->nullable()->after('datos_personales_omitidos');
            $table->unsignedSmallInteger('identificadores_residuales')->nullable()->after('sobreanonimizada');
            // 0–100: parecido entre el texto de la IA y el texto final guardado.
            $table->decimal('similitud_texto', 5, 2)->nullable()->after('identificadores_residuales');
            $table->timestamp('privacidad_evaluada_at')->nullable()->after('similitud_texto');

            // 0–100: subcategorías comunes / subcategorías distintas entre IA y final.
            $table->decimal('acierto_subcategorias', 5, 2)->nullable()->after('subcategorias_correctas');
            $table->boolean('propuesta_aplicada')->nullable()->after('urgencia_correcta');
        });
    }

    public function down(): void
    {
        Schema::table('propuestas_ia', function (Blueprint $table) {
            $table->dropColumn([
                'datos_personales_omitidos',
                'sobreanonimizada',
                'identificadores_residuales',
                'similitud_texto',
                'privacidad_evaluada_at',
                'acierto_subcategorias',
                'propuesta_aplicada',
            ]);
        });
    }
};
