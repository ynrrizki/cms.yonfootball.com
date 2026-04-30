<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OrderBacklogWidget extends StatsOverviewWidget
{
    protected int|string|array $columnSpan = 4;

    protected function getStats(): array
    {
        $backlogCount = Order::where('status', OrderStatus::PENDING)
            ->whereHas('transaction', fn ($q) => $q->where('status', 'PAID'))
            ->count();

        return [
            Stat::make('Backlog Siap Proses', $backlogCount)
                ->description('Order PAID yang belum diproses')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($backlogCount > 0 ? 'danger' : 'success'),
        ];
    }
}