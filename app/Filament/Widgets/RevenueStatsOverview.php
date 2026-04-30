<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class RevenueStatsOverview extends StatsOverviewWidget
{
    protected int|string|array $columnSpan = 4;

    protected function getStats(): array
    {
        $driver = DB::getDriverName();

        $jsonExpression = match ($driver) {
            'pgsql' => "(product_snapshot->>'price_final')::numeric",
            default => 'JSON_EXTRACT(product_snapshot, "$.price_final")',
        };

        $dailyRevenue = Transaction::where('status', 'PAID')
            ->whereDate('paid_at', today())
            ->sum(DB::raw($jsonExpression));

        $weeklyRevenue = Transaction::where('status', 'PAID')
            ->whereBetween('paid_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->sum(DB::raw($jsonExpression));

        $monthlyRevenue = Transaction::where('status', 'PAID')
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum(DB::raw($jsonExpression));

        return [
            Stat::make('Pendapatan Hari Ini', 'Rp ' . number_format($dailyRevenue, 0, ',', '.'))
                ->description('Total dari transaksi hari ini')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
            Stat::make('Minggu Ini', 'Rp ' . number_format($weeklyRevenue, 0, ',', '.'))
                ->description('Trend pendapatan mingguan'),
            Stat::make('Bulan Ini', 'Rp ' . number_format($monthlyRevenue, 0, ',', '.'))
                ->description('Target performa bulanan'),
        ];
    }
}
