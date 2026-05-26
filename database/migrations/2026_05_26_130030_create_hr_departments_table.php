<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_departments', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('code', 20)->unique()->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('hr_departments')->nullOnDelete();
            // manager_id ajouté après création de hr_employees (dépendance circulaire)
            $table->unsignedBigInteger('manager_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_departments');
    }
};
