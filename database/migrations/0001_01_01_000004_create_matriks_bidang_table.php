<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matriks_konversi', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('tingkatan_id');
            $table->unsignedTinyInteger('tahapan_id');
            $table->unsignedTinyInteger('min_sks')->nullable();
            $table->unsignedTinyInteger('max_sks')->nullable();
            $table->string('huruf_nilai', 5)->nullable();
            $table->tinyInteger('is_active')->default(1);
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->unique(['tingkatan_id', 'tahapan_id'], 'uq_matriks');
            $table->foreign('tingkatan_id')->references('id')->on('tingkatan_lomba');
            $table->foreign('tahapan_id')->references('id')->on('tahapan_lomba');
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('bidang_lomba', function (Blueprint $table) {
            $table->unsignedSmallInteger('id')->autoIncrement();
            $table->string('nama', 100);
            $table->text('keterangan')->nullable();
            $table->tinyInteger('is_active')->default(1);
            $table->timestamps();
        });

        Schema::create('mata_kuliah', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('prodi_id');
            $table->string('kode_mk', 20);
            $table->string('nama_mk', 200);
            $table->unsignedTinyInteger('sks');
            $table->unsignedTinyInteger('semester')->nullable();
            $table->tinyInteger('is_active')->default(1);
            $table->timestamps();

            $table->unique(['kode_mk', 'prodi_id'], 'uq_mk_prodi');
            $table->foreign('prodi_id')->references('id')->on('prodi');
        });

        Schema::create('bidang_mata_kuliah', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('bidang_id');
            $table->unsignedBigInteger('mata_kuliah_id');
            $table->tinyInteger('is_active')->default(1);
            $table->timestamps();

            $table->unique(['bidang_id', 'mata_kuliah_id'], 'uq_bidang_mk');
            $table->foreign('bidang_id')->references('id')->on('bidang_lomba')->cascadeOnDelete();
            $table->foreign('mata_kuliah_id')->references('id')->on('mata_kuliah')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bidang_mata_kuliah');
        Schema::dropIfExists('mata_kuliah');
        Schema::dropIfExists('bidang_lomba');
        Schema::dropIfExists('matriks_konversi');
    }
};
