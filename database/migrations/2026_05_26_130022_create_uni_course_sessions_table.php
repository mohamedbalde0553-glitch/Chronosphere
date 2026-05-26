<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('uni_course_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('uni_courses')->cascadeOnDelete();
            $table->foreignId('room_id')->nullable()->constrained('uni_rooms')->nullOnDelete();
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->string('week_parity', 5)->nullable();
            $table->string('session_type', 20)->default('lecture');
            $table->string('status', 20)->default('scheduled');
            $table->text('notes')->nullable();
            $table->boolean('is_makeup')->default(false);
            $table->foreignId('original_session_id')->nullable()->constrained('uni_course_sessions')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['start_at', 'end_at']);
            $table->index('course_id');
            $table->index('room_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('uni_course_sessions');
    }
};
