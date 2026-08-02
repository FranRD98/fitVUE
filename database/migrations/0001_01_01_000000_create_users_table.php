<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('role', ['user', 'coach', 'admin'])->default('user');
            $table->unsignedTinyInteger('plan_id')->default(1);
            $table->unsignedBigInteger('coach_uid')->nullable();
            $table->unsignedBigInteger('assigned_routine')->nullable();
            $table->unsignedBigInteger('assigned_routine_by_coach')->nullable();
            $table->unsignedBigInteger('assigned_diet')->nullable();
            $table->string('profile_image')->nullable();
            $table->boolean('completed_form')->default(false);

            // Datos del formulario de onboarding ("empezar").
            $table->date('birthday')->nullable();
            $table->string('gender')->nullable();
            $table->string('goal')->nullable();
            $table->decimal('height', 5, 1)->nullable();
            $table->decimal('weight', 5, 1)->nullable();
            $table->unsignedSmallInteger('age')->nullable();
            $table->string('activity')->nullable();

            $table->rememberToken();
            $table->timestamps();

            $table->foreign('coach_uid')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
