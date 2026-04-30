<?php

namespace App\Filament\Resources\Transactions\Schemas;

use App\Enums\TransactionStatus;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Grid;
use Filament\Schemas\Schema;

class TransactionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->schema([
                        Section::make('Info Pembayaran')
                            ->columnSpan(2)
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextEntry::make('invoice_number')
                                            ->weight('bold')
                                            ->copyable(),
                                        TextEntry::make('status')
                                            ->badge()
                                            ->color(fn (TransactionStatus $state): string => match ($state) {
                                                TransactionStatus::PENDING => 'warning',
                                                TransactionStatus::PAID => 'success',
                                                TransactionStatus::CANCELLED => 'danger',
                                                TransactionStatus::REFUNDED => 'gray',
                                            }),
                                        TextEntry::make('payment_method')
                                            ->badge()
                                            ->color('info'),
                                        TextEntry::make('paid_at')
                                            ->dateTime()
                                            ->placeholder('Belum dibayar'),
                                        TextEntry::make('payment_url')
                                            ->label('URL Pembayaran')
                                            ->url(fn ($record) => $record->payment_url)
                                            ->openUrlInNewTab()
                                            ->visible(fn ($record) => $record->status === TransactionStatus::PENDING && $record->payment_url)
                                            ->color('primary'),
                                    ]),
                            ]),
                        
                        Section::make('Info Customer')
                            ->columnSpan(1)
                            ->schema([
                                TextEntry::make('customer_name')->label('Nama'),
                                TextEntry::make('customer_email')->label('Email')->icon('heroicon-m-envelope'),
                                TextEntry::make('customer_phone')->label('Phone')->icon('heroicon-m-phone'),
                            ]),

                        Section::make('Produk')
                            ->columnSpan(2)
                            ->schema([
                                TextEntry::make('product_name')
                                    ->weight('bold')
                                    ->size('lg'),
                                TextEntry::make('product_snapshot')
                                    ->label('Snapshot Produk (JSON)')
                                    ->json()
                                    ->collapsible(),
                            ]),

                        Section::make('Pembayaran Provider')
                            ->columnSpan(1)
                            ->schema([
                                TextEntry::make('payment_snapshot')
                                    ->label('Snapshot Provider (JSON)')
                                    ->json()
                                    ->collapsible(),
                            ]),

                        Section::make('Order Terkait')
                            ->columnSpanFull()
                            ->schema([
                                TextEntry::make('order.ticket_number')
                                    ->label('Ticket Number')
                                    ->weight('bold')
                                    ->color('primary')
                                    ->url(fn ($record) => $record->order_id ? "/admin/orders/{$record->order_id}" : null),
                                TextEntry::make('order.status')
                                    ->label('Order Status')
                                    ->badge(),
                            ]),
                    ]),
            ]);
    }
}
