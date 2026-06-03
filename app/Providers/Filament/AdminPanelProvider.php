<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard;
use App\Filament\Widgets\AppointmentStatsWidget;
use App\Filament\Widgets\RecentAppointmentsWidget;
use App\Filament\Widgets\RevenueChartWidget;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
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
            ->profile()
            ->brandName('')
            ->brandLogo(fn () => \App\Models\SiteSetting::instance()->logoUrl())
            ->brandLogoHeight('2.5rem')
            ->favicon(fn () => \App\Models\SiteSetting::instance()->logoUrl())
            ->colors([
                'primary' => Color::Cyan,
                'danger'  => Color::Red,
                'success' => Color::Emerald,
                'warning' => Color::Amber,
                'info'    => Color::Sky,
                'gray'    => Color::Slate,
            ])
            ->sidebarCollapsibleOnDesktop()
            ->navigationGroups([
                NavigationGroup::make('Clinic Operations')
                    ->icon('heroicon-o-building-office-2')
                    ->collapsible(),
                NavigationGroup::make('Appointments')
                    ->icon('heroicon-o-calendar-days')
                    ->collapsible(),
                NavigationGroup::make('Medical Records')
                    ->icon('heroicon-o-document-text')
                    ->collapsible(),
                NavigationGroup::make('Billing & Payments')
                    ->icon('heroicon-o-banknotes')
                    ->collapsible(),
                NavigationGroup::make('Administration')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->collapsible(),
            ])
            ->userMenuItems([
                MenuItem::make()
                    ->label('Patient Portal')
                    ->url('/')
                    ->icon('heroicon-o-arrow-left-circle'),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                AccountWidget::class,
                AppointmentStatsWidget::class,
                RecentAppointmentsWidget::class,
                RevenueChartWidget::class,
            ])
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
            ])
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): string => '<style>.fi-sidebar-nav{scrollbar-width:none;-ms-overflow-style:none;}.fi-sidebar-nav::-webkit-scrollbar{display:none;}</style>',
            );
    }
}
