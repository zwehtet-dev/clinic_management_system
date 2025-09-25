<?php

namespace App\Providers\Filament;

use App\Filament\Widgets\DrugBatchExpiryWidget;
use App\Filament\Widgets\ExpenseCategoryChartWidget;
use App\Filament\Widgets\MonthlyComparisonWidget;
use App\Filament\Widgets\PatientVisitsChartWidget;
use App\Filament\Widgets\RevenueChartWidget;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Pages\Dashboard;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Support\Icons\Heroicon;
use App\Filament\Widgets\StatsOverview;
use Filament\Navigation\NavigationGroup;
use Filament\Widgets\FilamentInfoWidget;
use App\Filament\Widgets\StockAlertWidget;
use Filament\Http\Middleware\Authenticate;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Filament\Http\Middleware\AuthenticateSession;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('')
            ->login()
            ->colors([
                'primary' => Color::Green,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
//                AccountWidget::class,
//                FilamentInfoWidget::class,
                StatsOverview::class,
                // RevenueChartWidget::class,
                // PatientVisitsChartWidget::class,
                // ExpenseCategoryChartWidget::class,
                // MonthlyComparisonWidget::class,
                StockAlertWidget::class,
                DrugBatchExpiryWidget::class
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->globalSearch()
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->sidebarCollapsibleOnDesktop()
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('Patient Management')
                    ->icon(Heroicon::OutlinedUser),

                NavigationGroup::make()
                    ->label('Medical Services')
                    ->icon(Heroicon::OutlinedHeart),

                NavigationGroup::make()
                    ->label('Pharmacy')
                    ->icon(Heroicon::OutlinedBeaker),

                NavigationGroup::make()
                    ->label('Financial')
                    ->icon(Heroicon::OutlinedCurrencyDollar),

                NavigationGroup::make()
                    ->label('Reports')
                    ->icon(Heroicon::OutlinedChartBar),

                NavigationGroup::make()
                    ->label('User Management')
                    ->icon(Heroicon::OutlinedUserGroup),

                NavigationGroup::make()
                    ->label('System')
                    ->icon(Heroicon::OutlinedCog6Tooth),
            ]);
    }
}
