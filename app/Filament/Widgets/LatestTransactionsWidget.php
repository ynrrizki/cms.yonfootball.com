<?php

namespace App\Filament\Widgets;

use App\Enums\TransactionStatus;
use App\Models\Transaction;
use Filament\Actions\Action;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestTransactionsWidget extends BaseWidget
{
    protected static ?string $heading = 'Transaksi Terbaru';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Transaction::query()->latest()->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')->weight('bold'),
                Tables\Columns\TextColumn::make('customer_name')->label('Customer'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (TransactionStatus $state): string => match ($state) {
                        TransactionStatus::PENDING => 'warning',
                        TransactionStatus::PAID => 'success',
                        TransactionStatus::CANCELLED => 'danger',
                        TransactionStatus::REFUNDED => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->label('Waktu'),
            ])
            ->actions([
                Action::make('view')
                    ->url(fn (Transaction $record): string => "/admin/transactions/{$record->id}")
                    ->icon('heroicon-m-eye'),
            ]);
    }
}
