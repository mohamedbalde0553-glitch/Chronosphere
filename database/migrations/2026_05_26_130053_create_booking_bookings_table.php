<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resource_id')->constrained('booking_resources')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title', 191);
            $table->text('description')->nullable();
            $table->dateTime('start_at')->index();
            $table->dateTime('end_at')->index();
            $table->unsignedInteger('duration_minutes');
            $table->unsignedSmallInteger('attendees_count')->default(1);
            $table->string('status', 20)->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['start_at', 'end_at']);
            $table->index(['resource_id', 'start_at']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_bookings');
    }
};
