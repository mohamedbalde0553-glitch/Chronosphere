<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_waitlist', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resource_id')->constrained('booking_resources')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->dateTime('requested_start_at');
            $table->dateTime('requested_end_at');
            $table->unsignedInteger('duration_minutes');
            $table->string('status', 20)->default('waiting');
            $table->timestamp('notified_at')->nullable();
            $table->timestamps();

            $table->index(['resource_id', 'requested_start_at']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_waitlist');
    }
};
