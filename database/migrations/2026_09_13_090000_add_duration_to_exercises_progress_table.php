<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exercises_progress', function (Blueprint $table) {
            $table->unsignedInteger('duration_seconds')->nullable()->after('sets');
        });
    }

    public function down(): void
    {
        Schema::table('exercises_progress', function (Blueprint $table) {
            $table->dropColumn('duration_seconds');
        });
    }
};
