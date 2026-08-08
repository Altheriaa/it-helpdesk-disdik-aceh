<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName('E-Helpdesk Disdik')
            ->brandLogo(new HtmlString('<div style="display:flex;align-items:center;gap:10px;"><img src="/images/Logo.svg" alt="Logo" style="height:36px;width:36px;object-fit:contain;"> <span style="font-weight:700;font-size:1.1rem;">E-Helpdesk Disdik</span></div>'))
            ->brandLogoHeight('2.5rem')
            ->colors([
                'primary' => Color::Blue,
                'danger' => Color::Red,
                'warning' => Color::Amber,
                'success' => Color::Green,
                'info' => Color::Sky,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->navigationGroups([
                'Tiket',
                'Master Data',
                'Laporan',
            ])
            ->sidebarCollapsibleOnDesktop()
            ->renderHook(
                'panels::head.end',
                fn () => new HtmlString('
                    <style>
                        @import url("https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap");

                        /* ── Custom Professional Header & Filament Buttons ── */
                        .fi-ac-action-btn, .fi-btn {
                            font-family: "Plus Jakarta Sans", -apple-system, BlinkMacSystemFont, sans-serif !important;
                            font-weight: 600 !important;
                            border-radius: 0.625rem !important;
                            transition: all 0.15s ease !important;
                            letter-spacing: -0.01em !important;
                        }

                        /* Primary Action (e.g. Assign IT Support) */
                        .fi-ac-action-btn[data-color="primary"],
                        .fi-btn-color-primary {
                            background-color: #004799 !important;
                            color: #ffffff !important;
                            border: 1px solid #00387a !important;
                            box-shadow: 0 1px 3px rgba(0, 71, 153, 0.2) !important;
                        }
                        .fi-ac-action-btn[data-color="primary"]:hover,
                        .fi-btn-color-primary:hover {
                            background-color: #003370 !important;
                        }

                        /* Warning Action (e.g. Ubah Status) */
                        .fi-ac-action-btn[data-color="warning"],
                        .fi-btn-color-warning {
                            background-color: #fffbeb !important;
                            color: #b45309 !important;
                            border: 1px solid #fde68a !important;
                            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04) !important;
                        }
                        .fi-ac-action-btn[data-color="warning"]:hover,
                        .fi-btn-color-warning:hover {
                            background-color: #fef3c7 !important;
                            color: #92400e !important;
                        }

                        /* Success Action (e.g. Assign ke Saya) */
                        .fi-ac-action-btn[data-color="success"],
                        .fi-btn-color-success {
                            background-color: #ecfdf5 !important;
                            color: #047857 !important;
                            border: 1px solid #a7f3d0 !important;
                            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04) !important;
                        }
                        .fi-ac-action-btn[data-color="success"]:hover,
                        .fi-btn-color-success:hover {
                            background-color: #d1fae5 !important;
                            color: #065f46 !important;
                        }

                        /* Edit / Gray / Neutral Action */
                        .fi-ac-action-btn[data-color="gray"],
                        .fi-btn-color-gray {
                            background-color: #ffffff !important;
                            color: #334155 !important;
                            border: 1px solid #cbd5e1 !important;
                            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04) !important;
                        }
                        .fi-ac-action-btn[data-color="gray"]:hover,
                        .fi-btn-color-gray:hover {
                            background-color: #f8fafc !important;
                            color: #0f172a !important;
                            border-color: #94a3b8 !important;
                        }

                        /* Dark Mode Overrides */
                        .dark .fi-ac-action-btn[data-color="warning"],
                        .dark .fi-btn-color-warning {
                            background-color: #78350f !important;
                            color: #fef3c7 !important;
                            border-color: #92400e !important;
                        }
                        .dark .fi-ac-action-btn[data-color="gray"],
                        .dark .fi-btn-color-gray {
                            background-color: #0f172a !important;
                            color: #cbd5e1 !important;
                            border-color: #334155 !important;
                        }
                        .dark .fi-ac-action-btn[data-color="gray"]:hover,
                        .dark .fi-btn-color-gray:hover {
                            background-color: #1e293b !important;
                            color: #f8fafc !important;
                        }
                    </style>
                ')
            )
            ->databaseNotifications()
            ->databaseNotificationsPolling('5s')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
