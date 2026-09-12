<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exercises', function (Blueprint $table) {
            $table->enum('equipment', [
                'ninguno', 'banda_resistencia', 'banda_suspension', 'barra',
                'disco', 'mancuerna', 'maquina', 'pesa_rusa', 'otro',
            ])->nullable()->after('id_category');
        });
    }

    public function down(): void
    {
        Schema::table('exercises', function (Blueprint $table) {
            $table->dropColumn('equipment');
        });
    }
};
