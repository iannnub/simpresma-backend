<?php

namespace App\Providers;

use App\Events\PengajuanStatusChanged;
use App\Events\PengajuanSubmitted;
use App\Listeners\SendNewPengajuanNotification;
use App\Listeners\SendTelegramNotification;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Telegram notification event listeners
        Event::listen(PengajuanStatusChanged::class, SendTelegramNotification::class);
        Event::listen(PengajuanSubmitted::class, SendNewPengajuanNotification::class);

        // Security: Rate Limiters
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('login', function (Request $request) {
            $maxAttempts = app()->environment('production') ? 5 : 120;
            return Limit::perMinute($maxAttempts)->by($request->ip())->response(function () {
                return response()->json([
                    'success' => false,
                    'status'  => 'error',
                    'message' => 'Terlalu banyak percobaan login. Silakan tunggu 1 menit sebelum mencoba kembali.',
                ], 429);
            });
        });

        RateLimiter::for('submissions', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip())->response(function () {
                return response()->json([
                    'success' => false,
                    'status'  => 'error',
                    'message' => 'Terlalu banyak pengajuan dalam waktu singkat. Silakan tunggu beberapa saat.',
                ], 429);
            });
        });

        RateLimiter::for('telegram-webhook', function (Request $request) {
            return Limit::perMinute(60)->by($request->ip());
        });
    }
}
