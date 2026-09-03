<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prodi', function (Blueprint $table) {
            $table->unsignedTinyInteger('id')->autoIncrement();
            $table->string('kode', 10)->unique();
            $table->string('singkatan', 5)->unique();
            $table->string('nama', 100);
            $table->timestamps();
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->unsignedTinyInteger('id')->autoIncrement();
            $table->string('name', 30)->unique();
            $table->string('display_name', 50);
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nim_nip', 30)->nullable()->unique();
            $table->string('nama', 150);
            $table->string('email', 150)->unique();
            $table->string('password');
            $table->unsignedTinyInteger('prodi_id')->nullable();
            $table->string('no_whatsapp', 20)->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->foreign('prodi_id')->references('id')->on('prodi')->nullOnDelete();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('prodi');
    }
};
