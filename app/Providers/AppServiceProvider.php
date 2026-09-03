<?php

namespace App\Providers;

use App\Events\PengajuanStatusChanged;
use App\Events\PengajuanSubmitted;
use App\Listeners\SendNewPengajuanNotification;
use App\Listeners\SendTelegramNotification;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Telegram notification event listeners
        Event::listen(PengajuanStatusChanged::class, SendTelegramNotification::class);
        Event::listen(PengajuanSubmitted::class, SendNewPengajuanNotification::class);
    }
}
