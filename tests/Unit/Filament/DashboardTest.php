<?php

use App\Filament\Pages\Dashboard;
use App\Filament\Widgets\LatestTransactionsWidget;
use App\Filament\Widgets\PendingOrdersWidget;
use App\Filament\Widgets\RevenueLineChart;
use App\Filament\Widgets\RevenueStatsOverview;

it('registers only the dashboard widgets defined in the ux prd', function (): void {
    expect((new Dashboard())->getWidgets())->toBe([
        RevenueStatsOverview::class,
        RevenueLineChart::class,
        PendingOrdersWidget::class,
        LatestTransactionsWidget::class,
    ]);
});

it('uses a two-column layout for the lower section with full-width priority widgets', function (): void {
    expect((new Dashboard())->getColumns())->toBe([
        'md' => 2,
        'xl' => 2,
    ]);
});
