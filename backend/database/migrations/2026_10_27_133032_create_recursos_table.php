<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recursos', function (Blueprint $table) {
            $table->id();
            // Se fija al crear: cambiar el título no debe romper enlaces ya compartidos.
            $table->string('slug')->unique();
            $table->string('titulo');
            $table->string('tipo', 20)->default('infografia'); // clave de Recurso::TIPOS
            $table->string('tema', 30); // clave de Recurso::TEMAS
            // Markdown. Describe el contenido de la infografía: es su alternativa accesible.
            $table->text('descripcion');
            $table->string('autoria')->nullable();
            $table->string('licencia', 30)->nullable(); // clave de Recurso::LICENCIAS
            // Rutas dentro de public/. imagenes: una por página, en orden de lectura.
            $table->json('imagenes');
            $table->string('miniatura');
            $table->string('pdf')->nullable();
            $table->unsignedInteger('orden')->default(0);
            $table->boolean('publicado')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recursos');
    }
};
