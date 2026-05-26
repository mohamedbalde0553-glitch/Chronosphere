<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->string('attachable_type', 191);
            $table->unsignedBigInteger('attachable_id');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('filename', 191);
            $table->string('path', 500);
            $table->string('mime_type', 100);
            $table->unsignedInteger('size');
            $table->timestamp('created_at')->nullable();
            $table->softDeletes();

            $table->index(['attachable_type', 'attachable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
