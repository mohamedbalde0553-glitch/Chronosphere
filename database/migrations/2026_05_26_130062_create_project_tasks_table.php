<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('project_projects')->cascadeOnDelete();
            $table->foreignId('parent_task_id')->nullable()->constrained('project_tasks')->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('name', 191);
            $table->text('description')->nullable();
            $table->string('status', 20)->default('todo');
            $table->string('priority', 20)->default('medium');
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable()->index();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedInteger('estimated_minutes')->default(0);
            $table->unsignedInteger('actual_minutes')->default(0);
            $table->unsignedTinyInteger('progress')->default(0);
            $table->string('color', 7)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['project_id', 'status']);
            $table->index(['assigned_to', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_tasks');
    }
};
