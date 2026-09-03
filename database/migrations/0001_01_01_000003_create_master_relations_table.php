<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_roles', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->unsignedTinyInteger('role_id');
            $table->timestamp('created_at')->nullable();

            $table->primary(['user_id', 'role_id']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('role_id')->references('id')->on('roles')->cascadeOnDelete();
        });

        Schema::create('verifikator_prodi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedTinyInteger('prodi_id');
            $table->unsignedBigInteger('assigned_by');
            $table->tinyInteger('is_active')->default(1);
            $table->timestamps();

            $table->unique(['user_id', 'prodi_id']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('prodi_id')->references('id')->on('prodi');
            $table->foreign('assigned_by')->references('id')->on('users');
        });

        Schema::create('tingkatan_lomba', function (Blueprint $table) {
            $table->unsignedTinyInteger('id')->autoIncrement();
            $table->string('nama', 200);
            $table->unsignedTinyInteger('urutan');
        });

        Schema::create('tahapan_lomba', function (Blueprint $table) {
            $table->unsignedTinyInteger('id')->autoIncrement();
            $table->string('kode', 30)->unique();
            $table->string('nama', 100);
            $table->unsignedTinyInteger('urutan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tahapan_lomba');
        Schema::dropIfExists('tingkatan_lomba');
        Schema::dropIfExists('verifikator_prodi');
        Schema::dropIfExists('user_roles');
    }
};
