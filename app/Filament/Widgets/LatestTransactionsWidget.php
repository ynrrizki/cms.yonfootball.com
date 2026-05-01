<?php

namespace App\Filament\Widgets;

use App\Enums\TransactionStatus;
use App\Models\Transaction;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class LatestTransactionsWidget extends BaseWidget
{
    protected static ?string $heading = 'Transaksi Terbaru';

    protected ?string $description = 'Pantau aktivitas transaksi terakhir untuk validasi dan tindak lanjut cepat.';

    protected int|string|array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Transaction::query()->latest()->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')->weight('bold'),
                Tables\Columns\TextColumn::make('customer_name')->label('Customer'),
                Tables\Columns\TextColumn::make('product_name')
                    ->label('Produk')
                    ->limit(28),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (TransactionStatus $state): string => match ($state) {
                        TransactionStatus::PENDING => 'warning',
                        TransactionStatus::PAID => 'success',
                        TransactionStatus::CANCELLED => 'danger',
                        TransactionStatus::REFUNDED => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->label('Waktu')->since(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        TransactionStatus::PENDING->value => 'Pending',
                        TransactionStatus::PAID->value => 'Paid',
                        TransactionStatus::CANCELLED->value => 'Cancelled',
                        TransactionStatus::REFUNDED->value => 'Refunded',
                    ]),
                Tables\Filters\Filter::make('created_at')
                    ->label('Waktu Transaksi')
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
                    ->url(fn (Transaction $record): string => "/admin/transactions/{$record->id}")
                    ->icon('heroicon-m-eye'),
            ]);
    }
}
