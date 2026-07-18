<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('weight', 6, 2)->nullable();
            $table->decimal('neck', 6, 2)->nullable();
            $table->decimal('shoulders', 6, 2)->nullable();
            $table->decimal('chest', 6, 2)->nullable();
            $table->decimal('biceps_relaxed', 6, 2)->nullable();
            $table->decimal('biceps_flexed', 6, 2)->nullable();
            $table->decimal('forearm', 6, 2)->nullable();
            $table->decimal('wrist', 6, 2)->nullable();
            $table->decimal('waist', 6, 2)->nullable();
            $table->decimal('abdomen', 6, 2)->nullable();
            $table->decimal('hips', 6, 2)->nullable();
            $table->decimal('quadriceps', 6, 2)->nullable();
            $table->decimal('calves', 6, 2)->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('progress');
    }
};
