<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('routines', function (Blueprint $table) {
            $table->json('exercises')->nullable()->after('days');
        });

        // Una rutina pasa de tener varios días (cada uno con su lista de
        // ejercicios) a ser una única lista plana de ejercicios, estilo Hevy.
        // Se concatenan los ejercicios de todos los días, en orden.
        foreach (DB::table('routines')->select('id', 'days')->cursor() as $routine) {
            $days = json_decode($routine->days ?? '[]', true) ?: [];
            $exercises = [];

            foreach ($days as $day) {
                foreach ($day['exercises'] ?? [] as $exercise) {
                    $exercises[] = $exercise;
                }
            }

            DB::table('routines')->where('id', $routine->id)->update([
                'exercises' => json_encode($exercises),
            ]);
        }

        Schema::table('routines', function (Blueprint $table) {
            $table->dropColumn('days');
        });
    }

    public function down(): void
    {
        Schema::table('routines', function (Blueprint $table) {
            $table->json('days')->nullable()->after('id_category');
        });

        foreach (DB::table('routines')->select('id', 'exercises')->cursor() as $routine) {
            $exercises = json_decode($routine->exercises ?? '[]', true) ?: [];

            DB::table('routines')->where('id', $routine->id)->update([
                'days' => json_encode($exercises ? [['day' => 'Lunes', 'exercises' => $exercises]] : []),
            ]);
        }

        Schema::table('routines', function (Blueprint $table) {
            $table->dropColumn('exercises');
        });
    }
};
