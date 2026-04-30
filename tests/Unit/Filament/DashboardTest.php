<?php

use App\Filament\Pages\Dashboard;
use App\Filament\Widgets\LatestTransactionsWidget;
use App\Filament\Widgets\OrderBacklogWidget;
use App\Filament\Widgets\PendingOrdersWidget;
use App\Filament\Widgets\RevenueStatsOverview;
use App\Filament\Widgets\TransactionFunnelWidget;

function invokeProtectedDashboardMethod(object $object, string $method): mixed
{
    $reflectionClass = new ReflectionClass($object);
    $reflectionMethod = $reflectionClass->getMethod($method);
    $reflectionMethod->setAccessible(true);

    return $reflectionMethod->invoke($object);
}

it('registers only the dashboard widgets defined in the ux prd', function (): void {
    expect((new Dashboard())->getWidgets())->toBe([
        RevenueStatsOverview::class,
        OrderBacklogWidget::class,
        TransactionFunnelWidget::class,
        PendingOrdersWidget::class,
        LatestTransactionsWidget::class,
    ]);
});

it('shows all four transaction states in the funnel widget', function (): void {
    $stats = invokeProtectedDashboardMethod(new TransactionFunnelWidget(), 'getStats');

    expect($stats)->toHaveCount(4);
});

it('keeps the dashboard grid at twelve columns', function (): void {
    expect((new Dashboard())->getColumns())->toBe(12);
});
