<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Marca las sugerencias inventadas para probar la IA en la web publicada
 * (comando buzon:sinteticas). Así se pueden filtrar en las métricas, quedan
 * fuera de la gráfica pública y se borran todas de una vez.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sugerencias', function (Blueprint $table) {
            $table->boolean('sintetica')->default(false)->index()->after('estado');
        });
    }

    public function down(): void
    {
        Schema::table('sugerencias', function (Blueprint $table) {
            $table->dropIndex(['sintetica']);
            $table->dropColumn('sintetica');
        });
    }
};
