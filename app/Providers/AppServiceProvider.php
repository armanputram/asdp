<?php

namespace App\Providers;

use App\Listeners\SendMonitoringReminder;
use App\Models\Operasional;
use App\Observers\OperasionalObserver;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Observer: hapus notif otomatis kalau semua layanan sudah diinput
        Operasional::observe(OperasionalObserver::class);

        // Listener: kirim notif saat login kalau ada layanan belum dilaporkan
        \Event::listen(Login::class, SendMonitoringReminder::class);

        // Custom CSS Filament
        FilamentView::registerRenderHook(
            PanelsRenderHook::HEAD_END,
            fn(): string => view('filament.custom-styles')->render(),
        );
    }
}
