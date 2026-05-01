<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class PendingOrdersWidget extends BaseWidget
{
    protected static ?string $heading = 'Antrian Order Siap Proses';

    protected ?string $description = 'Fokuskan eksekusi pada order dengan transaksi PAID.';

    protected int|string|array $columnSpan = 1;

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
                Tables\Columns\TextColumn::make('ticket_number')
                    ->label('Ticket')
                    ->weight('bold')
                    ->searchable(),
                Tables\Columns\TextColumn::make('variant.name')
                    ->label('Produk')
                    ->limit(28)
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->label('Waktu Pesan')
                    ->since(),
            ])
            ->filters([
                Tables\Filters\Filter::make('created_at')
                    ->label('Waktu Pesan')
                    ->form([
                        DatePicker::make('from')->label('Dari'),
                        DatePicker::make('until')->label('Sampai'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date)
                            )
                            ->when(
                                $data['until'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date)
                            );
                    }),
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
