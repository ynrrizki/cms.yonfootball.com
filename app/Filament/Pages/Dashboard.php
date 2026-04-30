<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\LatestTransactionsWidget;
use App\Filament\Widgets\OrderBacklogWidget;
use App\Filament\Widgets\PendingOrdersWidget;
use App\Filament\Widgets\RevenueLineChart;
use App\Filament\Widgets\RevenueStatsOverview;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
	public function getWidgets(): array
	{
		return [
			RevenueStatsOverview::class,
			RevenueLineChart::class,
			OrderBacklogWidget::class,
			PendingOrdersWidget::class,
			LatestTransactionsWidget::class,
		];
	}

	public function getColumns(): int | array
	{
		return 12;
	}
}
