<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cal_event_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('cal_events')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->dateTime('remind_at')->index();
            $table->string('method', 20)->default('notification');
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'remind_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cal_event_reminders');
    }
};
