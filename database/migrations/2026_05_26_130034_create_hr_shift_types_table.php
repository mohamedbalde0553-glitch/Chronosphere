<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_shift_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->time('start_time');
            $table->time('end_time');
            $table->string('color', 7)->default('#10B981');
            $table->boolean('is_night')->default(false);
            $table->decimal('overtime_multiplier', 3, 2)->default(1.00);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_shift_types');
    }
};
