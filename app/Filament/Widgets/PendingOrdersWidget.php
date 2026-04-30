<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class PendingOrdersWidget extends BaseWidget
{
    protected static ?string $heading = 'Order Siap Proses';

    protected int|string|array $columnSpan = 8;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::query()
                    ->where('status', OrderStatus::PENDING)
                    ->whereHas('transaction', fn ($q) => $q->where('status', 'PAID'))
                    ->latest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('ticket_number')->weight('bold'),
                Tables\Columns\TextColumn::make('variant.name')->label('Produk'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->label('Waktu Pesan'),
            ])
            ->actions([
                Action::make('view')
                    ->url(fn (Order $record): string => "/admin/orders/{$record->id}")
                    ->icon('heroicon-m-eye')
                    ->button(),
            ])
            ->paginated([5, 10]);
    }
}
