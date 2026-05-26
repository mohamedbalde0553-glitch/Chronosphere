<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar', 255)->nullable()->after('password');
            $table->string('phone', 20)->nullable()->after('avatar');
            $table->string('language', 5)->default('fr')->after('phone');
            $table->string('theme', 10)->default('light')->after('language');
            $table->string('timezone', 50)->default('Europe/Paris')->after('theme');
            $table->string('default_module', 30)->nullable()->after('timezone');
            $table->boolean('is_active')->default(true)->after('default_module');
            $table->timestamp('last_login_at')->nullable()->after('is_active');

            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'avatar', 'phone', 'language', 'theme',
                'timezone', 'default_module', 'is_active', 'last_login_at',
            ]);
        });
    }
};
