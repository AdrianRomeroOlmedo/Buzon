<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Métricas de las partidas del escape room «Operación Verdad» (public/juegos/escape-room/).
        // Sin datos identificativos: cada partida se reconoce solo por el código aleatorio que genera
        // el juego. No se guarda la IP ni nada del navegador.
        Schema::create('partidas_escape_room', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique(); // p. ej. OV-7K3Q-M2PA
            $table->string('version_web', 60)->nullable();
            $table->string('estado', 40)->nullable(); // «En curso, en el módulo 2», «Finalizada»…
            $table->boolean('finalizada')->default(false);
            // Columna => valor: las mismas columnas que el CSV de «Datos de la partida».
            $table->json('datos');
            // Cuestionario de opinión, si se ha respondido (también con las columnas del CSV).
            $table->json('opinion')->nullable();
            $table->string('version_aviso', 20);
            $table->timestamp('fecha_aceptacion');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partidas_escape_room');
    }
};
