<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('uni_class_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('level_id')->constrained('uni_levels')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('uni_academic_years')->cascadeOnDelete();
            $table->string('name', 50);
            $table->string('type', 20)->default('class');
            $table->foreignId('parent_id')->nullable()->constrained('uni_class_groups')->nullOnDelete();
            $table->unsignedSmallInteger('capacity')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('uni_class_groups');
    }
};
