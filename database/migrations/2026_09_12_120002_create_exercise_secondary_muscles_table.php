<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL no revierte los CREATE TABLE ya ejecutados dentro de una
        // migración que falla después (el DDL hace commit implícito), así
        // que si esta migración falló a mitad la tabla puede haber quedado
        // creada sin el índice único. Se elimina primero para poder
        // reintentar de forma segura.
        Schema::dropIfExists('exercise_secondary_muscles');

        Schema::create('exercise_secondary_muscles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exercise_id')->constrained('exercises')->cascadeOnDelete();
            $table->foreignId('exercise_category_id')->constrained('exercises_categories')->cascadeOnDelete();
            $table->timestamps();

            // Nombre explícito y corto: el generado automáticamente por Laravel
            // supera el límite de 64 caracteres de un identificador en MySQL.
            $table->unique(['exercise_id', 'exercise_category_id'], 'exercise_secondary_muscles_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exercise_secondary_muscles');
    }
};
