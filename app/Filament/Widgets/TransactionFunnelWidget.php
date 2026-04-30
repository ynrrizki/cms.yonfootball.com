<?php

namespace App\Filament\Widgets;

use App\Enums\TransactionStatus;
use App\Models\Transaction;
use Filament\Widgets\ChartWidget;

class TransactionFunnelWidget extends ChartWidget
{
    protected ?string $heading = 'Status Transaksi (Hari Ini)';

    protected int|string|array $columnSpan = 6;

    protected function getData(): array
    {
        $pending = Transaction::where('status', TransactionStatus::PENDING)->whereDate('created_at', today())->count();
        $paid = Transaction::where('status', TransactionStatus::PAID)->whereDate('paid_at', today())->count();
        $cancelled = Transaction::where('status', TransactionStatus::CANCELLED)->whereDate('created_at', today())->count();
        $refunded = Transaction::where('status', TransactionStatus::REFUNDED)->whereDate('created_at', today())->count();

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Transaksi',
                    'data' => [$pending, $paid, $cancelled, $refunded],
                    'backgroundColor' => [
                        '#fbbf24', // warning
                        '#10b981', // success
                        '#ef4444', // danger
                        '#94a3b8', // gray
                    ],
                ],
            ],
            'labels' => ['Pending', 'Paid', 'Cancelled', 'Refunded'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
            ],
        ];
    }
}
