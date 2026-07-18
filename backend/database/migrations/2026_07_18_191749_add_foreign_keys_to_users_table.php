<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('assigned_routine')->references('id')->on('routines')->nullOnDelete();
            $table->foreign('assigned_routine_by_coach')->references('id')->on('routines')->nullOnDelete();
            $table->foreign('assigned_diet')->references('id')->on('diets')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['assigned_routine']);
            $table->dropForeign(['assigned_routine_by_coach']);
            $table->dropForeign(['assigned_diet']);
        });
    }
};
