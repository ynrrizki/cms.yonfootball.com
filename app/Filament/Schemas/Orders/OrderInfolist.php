<?php

namespace App\Filament\Schemas\Orders;

use App\Enums\OrderStatus;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->schema([
                        Section::make('Informasi Pesanan')
                            ->columnSpan(2)
                            ->columns(2)
                            ->schema([
                                TextEntry::make('ticket_number')
                                    ->label('Nomor Tiket')
                                    ->weight('bold')
                                    ->copyable(),
                                TextEntry::make('status')
                                    ->badge()
                                    ->color(fn (OrderStatus $state): string => match ($state) {
                                        OrderStatus::PENDING => 'warning',
                                        OrderStatus::PROCESSING => 'info',
                                        OrderStatus::COMPLETED => 'success',
                                        OrderStatus::FAILED => 'danger',
                                    }),
                                TextEntry::make('transaction.external_id')
                                    ->label('ID Transaksi')
                                    ->placeholder('N/A'),
                                TextEntry::make('processedBy.name')
                                    ->label('Admin Penanggung Jawab')
                                    ->placeholder('System'),
                            ]),
                        Section::make('Detail Produk')
                            ->columnSpan(1)
                            ->schema([
                                TextEntry::make('variant.name')
                                    ->label('Varian Produk'),
                                TextEntry::make('variant.product.name')
                                    ->label('Nama Produk'),
                                TextEntry::make('created_at')
                                    ->label('Waktu Pesan')
                                    ->dateTime(),
                            ]),
                    ]),

                Section::make('Riwayat Status')
                    ->description('Timeline perubahan status pesanan')
                    ->schema([
                        RepeatableEntry::make('histories')
                            ->label('')
                            ->schema([
                                Grid::make(4)
                                    ->schema([
                                        TextEntry::make('status')
                                            ->badge()
                                            ->color(fn (OrderStatus $state): string => match ($state) {
                                                OrderStatus::PENDING => 'warning',
                                                OrderStatus::PROCESSING => 'info',
                                                OrderStatus::COMPLETED => 'success',
                                                OrderStatus::FAILED => 'danger',
                                            }),
                                        TextEntry::make('message')
                                            ->label('Catatan'),
                                        TextEntry::make('user.name')
                                            ->label('Oleh')
                                            ->placeholder('System'),
                                        TextEntry::make('created_at')
                                            ->label('Waktu')
                                            ->dateTime(),
                                    ]),
                            ])
                            ->columns(1),
                    ]),
            ]);
    }
}
