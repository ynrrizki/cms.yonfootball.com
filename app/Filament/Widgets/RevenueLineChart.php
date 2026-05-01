<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class RevenueLineChart extends ChartWidget
{
    protected ?string $heading = 'Tren Pendapatan';

    protected ?string $description = 'Memantau momentum revenue harian berdasarkan rentang waktu yang dipilih.';

    protected int|string|array $columnSpan = 'full';

    public ?string $filter = '7';

    protected function getData(): array
    {
        $days = (int) ($this->filter ?? '7');
        $startDate = now()->subDays($days - 1)->startOfDay();

        $driver = DB::getDriverName();
        $jsonExpression = match ($driver) {
            'pgsql' => "(product_snapshot->>'price_final')::numeric",
            default => 'JSON_EXTRACT(product_snapshot, "$.price_final")',
        };

        $data = Transaction::where('status', 'PAID')
            ->where('paid_at', '>=', $startDate)
            ->select([
                DB::raw('DATE(paid_at) as date'),
                DB::raw("SUM($jsonExpression) as total"),
            ])
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('total', 'date');

        $labels = [];
        $values = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $labels[] = now()->subDays($i)->format('d M');
            $values[] = (float) ($data[$date] ?? 0);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Pendapatan',
                    'data' => $values,
                    'fill' => 'start',
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getFilters(): ?array
    {
        return [
            '7' => '7 hari',
            '14' => '14 hari',
            '30' => '30 hari',
        ];
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
                ],
            ],
        ];
    }
}
