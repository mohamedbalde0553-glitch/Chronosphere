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
        Schema::table('uni_teachers', function (Blueprint $table) {
            $table->string('speciality', 100)->nullable()->after('title');
            $table->string('contract_type', 20)->nullable()->after('speciality');
            $table->boolean('is_active')->default(true)->after('contract_type');
            $table->dropColumn('specialty');
        });
    }

    public function down(): void
    {
        Schema::table('uni_teachers', function (Blueprint $table) {
            $table->dropColumn(['speciality', 'contract_type', 'is_active']);
        });
    }
};
