<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_resource_availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resource_id')->constrained('booking_resources')->cascadeOnDelete();
            $table->tinyInteger('day_of_week')->nullable()->comment('0=Sun,6=Sat; null=specific date');
            $table->date('specific_date')->nullable();
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_closed')->default(false);
            $table->timestamps();

            $table->index(['resource_id', 'day_of_week']);
            $table->index(['resource_id', 'specific_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_resource_availabilities');
    }
};
