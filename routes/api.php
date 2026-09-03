<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — SIMPRESMA
|--------------------------------------------------------------------------
*/

// ─── Public (tanpa auth) ─────────────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
});

// Telegram Webhook (tanpa auth — Telegram tidak mengirim Bearer token)
Route::post('telegram/webhook', [\App\Http\Controllers\TelegramWebhookController::class, 'handle']);

// ─── Auth Protected ──────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
        Route::post('connect-telegram', [AuthController::class, 'connectTelegram']);
        Route::post('disconnect-telegram', [AuthController::class, 'disconnectTelegram']);
    });

    // Ref (Master & Lookup data untuk form)
    Route::prefix('ref')->group(function () {
        Route::get('prodi', [\App\Http\Controllers\RefController::class, 'prodi']);
        Route::get('tingkatan', [\App\Http\Controllers\RefController::class, 'tingkatan']);
        Route::get('tahapan', [\App\Http\Controllers\RefController::class, 'tahapan']);
        Route::get('bidang', [\App\Http\Controllers\RefController::class, 'bidang']);
        Route::get('matriks', [\App\Http\Controllers\RefController::class, 'matriks']);
        Route::get('mata-kuliah', [\App\Http\Controllers\RefController::class, 'mataKuliah']);
    });

    // ─── Role-Protected Groups ─────────────────────────────────────────
    // Mahasiswa
    Route::middleware('role:mahasiswa')->prefix('mahasiswa')->group(function () {
        Route::get('pengajuan', [\App\Http\Controllers\Mahasiswa\PengajuanController::class, 'index']);
        Route::post('pengajuan', [\App\Http\Controllers\Mahasiswa\PengajuanController::class, 'store']);
        Route::get('pengajuan/{id}', [\App\Http\Controllers\Mahasiswa\PengajuanController::class, 'show']);
    });

    // Verifikator
    Route::middleware('role:verifikator')->prefix('verifikator')->group(function () {
        Route::get('pengajuan', [\App\Http\Controllers\Verifikator\PengajuanController::class, 'index']);
        Route::get('pengajuan/{id}', [\App\Http\Controllers\Verifikator\PengajuanController::class, 'show']);
        Route::post('pengajuan/{id}/terima', [\App\Http\Controllers\Verifikator\PengajuanController::class, 'terima']);
        Route::post('pengajuan/{id}/tolak', [\App\Http\Controllers\Verifikator\PengajuanController::class, 'tolak']);
    });

    // Tendik
    Route::middleware('role:tendik')->prefix('tendik')->group(function () {
        Route::get('pengajuan', [\App\Http\Controllers\Tendik\PengajuanController::class, 'index']);
        Route::get('pengajuan/{id}', [\App\Http\Controllers\Tendik\PengajuanController::class, 'show']);
        Route::post('pengajuan/{id}/finalisasi', [\App\Http\Controllers\Tendik\PengajuanController::class, 'finalisasi']);
        Route::get('export', [\App\Http\Controllers\Tendik\ExportController::class, 'export']);
    });

    // Wadek
    Route::middleware('role:wadek')->prefix('wadek')->group(function () {
        // T10.1: Matriks Konversi
        Route::get('matriks', [\App\Http\Controllers\Wadek\MatriksController::class, 'index']);
        Route::put('matriks/{id}', [\App\Http\Controllers\Wadek\MatriksController::class, 'update']);

        // T10.2: Manajemen Tim Verifikator
        Route::get('verifikator', [\App\Http\Controllers\Wadek\VerifikatorController::class, 'index']);
        Route::post('verifikator', [\App\Http\Controllers\Wadek\VerifikatorController::class, 'store']);
        Route::delete('verifikator/{id}', [\App\Http\Controllers\Wadek\VerifikatorController::class, 'destroy']);

        // T10.3: Mapping Bidang → Mata Kuliah
        Route::get('bidang-mata-kuliah', [\App\Http\Controllers\Wadek\BidangMataKuliahController::class, 'index']);
        Route::post('bidang-mata-kuliah', [\App\Http\Controllers\Wadek\BidangMataKuliahController::class, 'store']);
        Route::delete('bidang-mata-kuliah/{id}', [\App\Http\Controllers\Wadek\BidangMataKuliahController::class, 'destroy']);

        // T10.4: Export Data
        Route::get('export', [\App\Http\Controllers\Wadek\ExportController::class, 'export']);
    });

    // Admin (Super User / Role Management)
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('users', [\App\Http\Controllers\AdminController::class, 'index']);
        Route::post('users/{id}/roles', [\App\Http\Controllers\AdminController::class, 'assignRole']);
        Route::delete('users/{id}/roles/{roleName}', [\App\Http\Controllers\AdminController::class, 'revokeRole']);
        Route::post('users/{id}/verifikator-prodi', [\App\Http\Controllers\AdminController::class, 'assignVerifikatorProdi']);
        Route::delete('users/{id}/verifikator-prodi/{prodiId}', [\App\Http\Controllers\AdminController::class, 'revokeVerifikatorProdi']);
        Route::get('role-history', [\App\Http\Controllers\AdminController::class, 'roleHistory']);
        Route::get('available-roles', [\App\Http\Controllers\AdminController::class, 'availableRoles']);
    });

    // Shared (semua role yang terautentikasi)
    Route::get('dashboard/statistik', [\App\Http\Controllers\Shared\DashboardController::class, 'statistik']);
    Route::get('direktori-verifikator', [\App\Http\Controllers\Shared\VerifikatorDirektoriController::class, 'index']);
});
