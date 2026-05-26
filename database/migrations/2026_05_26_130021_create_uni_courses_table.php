<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('uni_courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained('uni_subjects')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('uni_teachers')->cascadeOnDelete();
            $table->foreignId('class_group_id')->constrained('uni_class_groups')->cascadeOnDelete();
            $table->foreignId('semester_id')->constrained('uni_semesters')->cascadeOnDelete();
            $table->unsignedInteger('weekly_volume_minutes')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('uni_courses');
    }
};
