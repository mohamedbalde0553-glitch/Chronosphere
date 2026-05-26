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
        // shift_type_id doit être nullable (shift sans type autorisé)
        Schema::table('hr_shifts', function (Blueprint $table) {
            $table->foreignId('shift_type_id')->nullable()->change();
        });

        // Ajouter photo_url aux employés
        Schema::table('hr_employees', function (Blueprint $table) {
            $table->string('photo_url')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('hr_shifts', function (Blueprint $table) {
            $table->foreignId('shift_type_id')->nullable(false)->change();
        });

        Schema::table('hr_employees', function (Blueprint $table) {
            $table->dropColumn('photo_url');
        });
    }
};
