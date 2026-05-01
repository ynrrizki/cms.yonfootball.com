<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Number;

class RevenueStatsOverview extends StatsOverviewWidget
{
    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = 'Ringkasan Performa';

    protected function getColumns(): int|array
    {
        return [
            'md' => 2,
            'xl' => 2,
            '2xl' => 3,
        ];
    }

    protected function getStats(): array
    {
        $driver = DB::getDriverName();

        $jsonExpression = match ($driver) {
            'pgsql' => "(product_snapshot->>'price_final')::numeric",
            default => 'JSON_EXTRACT(product_snapshot, "$.price_final")',
        };

        $dailyRevenue = Transaction::query()->where('status', 'PAID')
            ->whereDate('paid_at', '=', today(), 'and')
            ->sum(DB::raw($jsonExpression));

        $monthlyRevenue = Transaction::query()->where('status', 'PAID')
            ->whereMonth('paid_at', '=', now()->month, 'and')
            ->whereYear('paid_at', '=', now()->year, 'and')
            ->sum(DB::raw($jsonExpression));

        $todayPaidCount = Transaction::query()->where('status', 'PAID')
            ->whereDate('paid_at', '=', today(), 'and')
            ->count('*');

        $todayPendingCount = Transaction::query()->where('status', 'PENDING')
            ->whereDate('created_at', '=', today(), 'and')
            ->count('*');

        return [
            Stat::make('Pendapatan Hari Ini', Number::currency($dailyRevenue, 'IDR', 'id'))
                ->description("{$todayPaidCount} transaksi sukses hari ini")
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
            Stat::make('Pendapatan Bulan Ini', Number::currency($monthlyRevenue, 'IDR', 'id'))
                ->description('Performa revenue bulan berjalan')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('primary'),
            Stat::make('Pending Hari Ini', Number::format($todayPendingCount))
                ->description('Transaksi pending yang butuh follow-up')
                ->descriptionIcon('heroicon-m-clock')
                ->color($todayPendingCount > 0 ? 'danger' : 'success'),
        ];
    }
}
