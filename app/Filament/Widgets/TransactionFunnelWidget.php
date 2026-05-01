<?php

namespace App\Filament\Widgets;

use App\Enums\TransactionStatus;
use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TransactionFunnelWidget extends StatsOverviewWidget
{
    protected ?string $heading = 'Funnel Transaksi Harian';

    protected ?string $description = 'Breakdown status transaksi hari ini untuk identifikasi bottleneck funnel.';

    protected int|string|array $columnSpan = 4;

    protected function getStats(): array
    {
        $pending = Transaction::where('status', TransactionStatus::PENDING)->whereDate('created_at', today())->count();
        $paid = Transaction::where('status', TransactionStatus::PAID)->whereDate('paid_at', today())->count();
        $cancelled = Transaction::where('status', TransactionStatus::CANCELLED)->whereDate('created_at', today())->count();
        $refunded = Transaction::where('status', TransactionStatus::REFUNDED)->whereDate('created_at', today())->count();

        return [
            Stat::make('Pending', (string) $pending)
                ->description('Menunggu pembayaran')
                ->color('warning'),
            Stat::make('Paid', (string) $paid)
                ->description('Pembayaran terkonfirmasi')
                ->color('success'),
            Stat::make('Cancelled', (string) $cancelled)
                ->description('Dibatalkan pelanggan/sistem')
                ->color('danger'),
            Stat::make('Refunded', (string) $refunded)
                ->description('Dana dikembalikan')
                ->color('gray'),
        ];
    }
}
