<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cal_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('calendar_id')->constrained('cal_calendars')->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('cal_event_categories')->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_event_id')->nullable()->constrained('cal_events')->nullOnDelete();
            $table->string('title', 191);
            $table->text('description')->nullable();
            $table->dateTime('start_at')->index();
            $table->dateTime('end_at')->index();
            $table->boolean('is_all_day')->default(false);
            $table->string('location', 191)->nullable();
            $table->string('url', 191)->nullable();
            $table->string('color', 7)->nullable();
            $table->string('status', 20)->default('confirmed');
            $table->string('visibility', 20)->default('public');
            $table->string('recurrence_rule', 191)->nullable();
            $table->dateTime('recurrence_end_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['start_at', 'end_at']);
            $table->index(['calendar_id', 'start_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cal_events');
    }
};
