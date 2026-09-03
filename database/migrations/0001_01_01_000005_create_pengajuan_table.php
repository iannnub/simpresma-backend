<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengajuan', function (Blueprint $table) {
            $table->id();

            // Identitas Mahasiswa (snapshot saat submit)
            $table->unsignedBigInteger('user_id');
            $table->unsignedTinyInteger('prodi_id'); // snapshot prodi mahasiswa

            // Data Tim & Kontak
            $table->string('nama_tim', 150)->nullable();
            $table->string('no_whatsapp', 20);

            // Data Lomba
            $table->string('nama_lomba', 200);
            $table->unsignedSmallInteger('bidang_id');
            $table->unsignedTinyInteger('tingkatan_id');
            $table->unsignedTinyInteger('tahapan_id');
            $table->string('detail_juara', 50)->nullable(); // "Juara 1", "Juara 2", "HM", dll

            // Snapshot Matriks (diambil server saat POST submit — immutable)
            $table->unsignedTinyInteger('snapshot_min_sks')->nullable();
            $table->unsignedTinyInteger('snapshot_max_sks')->nullable();
            $table->string('snapshot_huruf_nilai', 5)->nullable();

            // Dokumen — Semua berupa Link/URL (tidak ada upload file)
            $table->string('link_sertifikat', 500);
            $table->tinyInteger('status_surat_tugas_mahasiswa')->default(0);
            $table->string('link_surat_tugas_mahasiswa', 500)->nullable();
            $table->tinyInteger('status_surat_tugas_dosen')->default(0);
            $table->string('link_surat_tugas_dosen', 500)->nullable();
            $table->string('link_poster', 500)->nullable();
            $table->string('link_sosmed', 500)->nullable();
            $table->text('keterangan')->nullable();

            // Status & Alur
            $table->enum('status', ['pending', 'diterima', 'ditolak', 'selesai'])->default('pending');

            // Verifikator
            $table->text('feedback_verifikator')->nullable(); // wajib jika ditolak
            $table->unsignedBigInteger('verifikator_id')->nullable();
            $table->timestamp('verified_at')->nullable();

            // Tendik
            $table->string('link_sk_konversi', 500)->nullable();
            $table->unsignedBigInteger('tendik_id')->nullable();
            $table->timestamp('processed_at')->nullable();

            $table->timestamps();

            // Foreign Keys
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('prodi_id')->references('id')->on('prodi');
            $table->foreign('bidang_id')->references('id')->on('bidang_lomba');
            $table->foreign('tingkatan_id')->references('id')->on('tingkatan_lomba');
            $table->foreign('tahapan_id')->references('id')->on('tahapan_lomba');
            $table->foreign('verifikator_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('tendik_id')->references('id')->on('users')->nullOnDelete();

            // Indexes
            $table->index(['prodi_id', 'status'], 'idx_pengajuan_prodi_status');
            $table->index('user_id', 'idx_pengajuan_user');
        });

        Schema::create('pengajuan_mata_kuliah', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pengajuan_id');
            $table->unsignedBigInteger('mata_kuliah_id');
            $table->unsignedTinyInteger('sks_snapshot'); // snapshot SKS MK saat mahasiswa pilih
            $table->string('huruf_nilai', 5)->nullable(); // diisi Tendik saat finalisasi

            $table->unique(['pengajuan_id', 'mata_kuliah_id'], 'uq_pengajuan_mk');
            $table->foreign('pengajuan_id')->references('id')->on('pengajuan')->cascadeOnDelete();
            $table->foreign('mata_kuliah_id')->references('id')->on('mata_kuliah');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajuan_mata_kuliah');
        Schema::dropIfExists('pengajuan');
    }
};
