<?php

namespace App\Filament\Resources\Transactions\Tables;

use App\Enums\TransactionStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TransactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice_number')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (TransactionStatus $state): string => match ($state) {
                        TransactionStatus::PENDING => 'warning',
                        TransactionStatus::PAID => 'success',
                        TransactionStatus::CANCELLED => 'danger',
                        TransactionStatus::REFUNDED => 'gray',
                    })
                    ->searchable(),
                TextColumn::make('customer_name')
                    ->label('Customer')
                    ->description(fn ($record) => $record->customer_email)
                    ->searchable(['customer_name', 'customer_email', 'customer_phone']),
                TextColumn::make('product_name')
                    ->label('Product')
                    ->limit(20),
                TextColumn::make('payment_method')
                    ->badge()
                    ->color('info'),
                TextColumn::make('paid_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'PENDING' => 'Pending',
                        'PAID' => 'Paid',
                        'CANCELLED' => 'Cancelled',
                        'REFUNDED' => 'Refunded',
                    ]),
                SelectFilter::make('payment_method')
                    ->options([
                        'QRIS' => 'QRIS',
                        'VA_BCA' => 'BCA Virtual Account',
                        'VA_MANDIRI' => 'Mandiri Virtual Account',
                    ]),
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from'),
                        DatePicker::make('created_until'),
                    ])
                    ->query(fn ($query, array $data) => $query
                        ->when($data['created_from'], fn ($query, $date) => $query->whereDate('created_at', '>=', $date))
                        ->when($data['created_until'], fn ($query, $date) => $query->whereDate('created_at', '<=', $date))),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    Action::make('markPaid')
                        ->label('Tandai PAID')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn ($record): bool => $record->status === TransactionStatus::PENDING)
                        ->action(fn ($record) => $record->update([
                            'status' => TransactionStatus::PAID,
                            'paid_at' => now(),
                        ]))
                        ->requiresConfirmation(),
                    Action::make('markCancelled')
                        ->label('Tandai CANCELLED')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(fn ($record): bool => $record->status === TransactionStatus::PENDING)
                        ->form([
                            \Filament\Forms\Components\Textarea::make('reason')
                                ->label('Alasan Pembatalan')
                                ->required(),
                        ])
                        ->action(fn ($record, array $data) => $record->update([
                            'status' => TransactionStatus::CANCELLED,
                            'payment_snapshot' => array_merge($record->payment_snapshot ?? [], ['cancel_reason' => $data['reason']]),
                        ]))
                        ->requiresConfirmation(),
                    Action::make('refund')
                        ->label('Proses Refund')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->color('gray')
                        ->visible(fn ($record): bool => $record->status === TransactionStatus::PAID)
                        ->form([
                            \Filament\Forms\Components\Textarea::make('reason')
                                ->label('Alasan Refund')
                                ->required(),
                        ])
                        ->action(fn ($record, array $data) => $record->update([
                            'status' => TransactionStatus::REFUNDED,
                            'payment_snapshot' => array_merge($record->payment_snapshot ?? [], ['refund_reason' => $data['reason']]),
                        ]))
                        ->requiresConfirmation(),
                ]),
            ])
            ->toolbarActions([
                // Read-only mostly, no bulk delete recommended for transactions
            ]);
    }
}
