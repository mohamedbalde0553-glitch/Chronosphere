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
        Schema::table('hr_departments', function (Blueprint $table) {
            $table->string('color', 7)->default('#059669')->after('code');
            $table->text('description')->nullable()->after('color');
        });
    }

    public function down(): void
    {
        Schema::table('hr_departments', function (Blueprint $table) {
            $table->dropColumn(['color', 'description']);
        });
    }
};
