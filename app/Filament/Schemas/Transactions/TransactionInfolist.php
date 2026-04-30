<?php

namespace App\Filament\Schemas\Transactions;

use App\Enums\TransactionStatus;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class TransactionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detail Transaksi')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('external_id')
                            ->label('ID Transaksi (Payment Gateway)')
                            ->weight('bold')
                            ->copyable(),
                        TextEntry::make('status')
                            ->badge()
                            ->color(fn (TransactionStatus $state): string => match ($state) {
                                TransactionStatus::PENDING => 'warning',
                                TransactionStatus::PAID => 'success',
                                TransactionStatus::EXPIRED => 'danger',
                                TransactionStatus::FAILED => 'danger',
                            }),
                        TextEntry::make('payment_method')
                            ->label('Metode Pembayaran'),
                        TextEntry::make('paid_at')
                            ->label('Waktu Bayar')
                            ->dateTime()
                            ->placeholder('Belum dibayar'),
                    ]),

                Section::make('Informasi Produk')
                    ->schema([
                        KeyValueEntry::make('product_snapshot')
                            ->label('Snapshot Produk Saat Transaksi')
                            ->columns(2),
                    ]),
            ]);
    }
}
