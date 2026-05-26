<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cal_event_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('calendar_id')->constrained('cal_calendars')->cascadeOnDelete();
            $table->foreignId('shared_with_id')->constrained('users')->cascadeOnDelete();
            $table->string('permission', 20)->default('view');
            $table->timestamps();

            $table->unique(['calendar_id', 'shared_with_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cal_event_shares');
    }
};
