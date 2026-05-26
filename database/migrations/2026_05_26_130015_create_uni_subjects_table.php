<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('uni_subjects', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->decimal('coefficient', 3, 1)->default(1.0);
            $table->unsignedTinyInteger('ects')->default(0);
            $table->string('color', 7)->default('#3B82F6');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('uni_subjects');
    }
};
