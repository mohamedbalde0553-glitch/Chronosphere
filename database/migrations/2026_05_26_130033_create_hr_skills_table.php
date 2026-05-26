<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_skills', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('category', 50)->nullable();
        });

        Schema::create('hr_employee_skill', function (Blueprint $table) {
            $table->foreignId('employee_id')->constrained('hr_employees')->cascadeOnDelete();
            $table->foreignId('skill_id')->constrained('hr_skills')->cascadeOnDelete();
            $table->tinyInteger('level')->default(1);

            $table->primary(['employee_id', 'skill_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_employee_skill');
        Schema::dropIfExists('hr_skills');
    }
};
