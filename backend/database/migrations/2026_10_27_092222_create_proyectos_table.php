<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proyectos', function (Blueprint $table) {
            $table->id();
            // Se fija al crear el proyecto: cambiar el título no debe romper enlaces ya compartidos.
            $table->string('slug')->unique();
            $table->string('titulo');
            $table->string('resumen', 300);
            $table->text('descripcion'); // Markdown
            $table->string('imagen')->nullable(); // ruta dentro de public/, opcional
            $table->string('linea', 30); // clave de Proyecto::LINEAS
            $table->string('estado', 20)->default('en-marcha'); // clave de Proyecto::ESTADOS
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->string('socios')->nullable();
            $table->string('enlace_url')->nullable();
            $table->string('enlace_texto')->nullable();
            $table->boolean('destacado')->default(false);
            $table->boolean('publicado')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proyectos');
    }
};
