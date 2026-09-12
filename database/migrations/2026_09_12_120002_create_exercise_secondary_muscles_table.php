<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exercise_secondary_muscles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exercise_id')->constrained('exercises')->cascadeOnDelete();
            $table->foreignId('exercise_category_id')->constrained('exercises_categories')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['exercise_id', 'exercise_category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exercise_secondary_muscles');
    }
};
