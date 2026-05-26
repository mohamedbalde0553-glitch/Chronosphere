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
        Schema::table('hr_employees', function (Blueprint $table) {
            $table->string('status', 20)->default('active')->after('contract_type');
        });
    }

    public function down(): void
    {
        Schema::table('hr_employees', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
