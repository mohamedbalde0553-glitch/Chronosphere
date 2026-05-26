<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('employee_code', 20)->unique();
            $table->foreignId('department_id')->constrained('hr_departments')->cascadeOnDelete();
            $table->foreignId('position_id')->constrained('hr_positions')->cascadeOnDelete();
            $table->string('contract_type', 20)->default('CDI');
            $table->date('hire_date');
            $table->date('end_date')->nullable();
            $table->unsignedInteger('weekly_hours_minutes')->default(2100);
            $table->unsignedInteger('max_daily_minutes')->default(600);
            $table->unsignedInteger('min_rest_minutes')->default(660);
            $table->timestamps();
            $table->softDeletes();
        });

        // Résolution dépendance circulaire : FK manager_id sur hr_departments
        Schema::table('hr_departments', function (Blueprint $table) {
            $table->foreign('manager_id')
                ->references('id')
                ->on('hr_employees')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('hr_departments', function (Blueprint $table) {
            $table->dropForeign(['manager_id']);
        });
        Schema::dropIfExists('hr_employees');
    }
};
