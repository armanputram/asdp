<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Menambahkan custom CSS untuk Filament
        FilamentView::registerRenderHook(
            PanelsRenderHook::HEAD_END,
            fn (): string => view('filament.custom-styles')->render(),
        );
    }
}
