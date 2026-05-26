<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('booking_resource_categories')->nullOnDelete();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('capacity')->default(1);
            $table->string('location', 191)->nullable();
            $table->string('color', 7)->default('#EA580C');
            $table->boolean('is_active')->default(true);
            $table->json('equipments')->nullable();
            $table->boolean('requires_approval')->default(false);
            $table->unsignedSmallInteger('advance_booking_days')->default(30);
            $table->unsignedInteger('max_booking_duration_minutes')->default(480);
            $table->timestamps();
            $table->softDeletes();

            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_resources');
    }
};
