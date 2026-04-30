<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\OrderStatus;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Grid;
use Filament\Schemas\Schema;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->schema([
                        Section::make('Detail Order')
                            ->columnSpan(2)
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextEntry::make('ticket_number')
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
                                        TextEntry::make('variant.name')
                                            ->label('Produk Varian'),
                                        TextEntry::make('created_at')
                                            ->label('Waktu Pesan')
                                            ->dateTime(),
                                    ]),
                                TextEntry::make('user_inputs')
                                    ->label('User Inputs (Form Data)')
                                    ->json()
                                    ->columnSpanFull(),
                            ]),

                        Section::make('Status Transaksi Terkait')
                            ->columnSpan(1)
                            ->schema([
                                TextEntry::make('transaction.invoice_number')
                                    ->label('Invoice')
                                    ->weight('bold')
                                    ->url(fn ($record) => $record->transaction ? "/admin/transactions/{$record->transaction->id}" : null)
                                    ->color('primary'),
                                TextEntry::make('transaction.status')
                                    ->label('Payment Status')
                                    ->badge(),
                                TextEntry::make('transaction.payment_method')
                                    ->label('Metode Bayar'),
                            ]),

                        Section::make('Timeline Order')
                            ->columnSpan(2)
                            ->schema([
                                RepeatableEntry::make('histories')
                                    ->label('Riwayat Perubahan Status')
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                TextEntry::make('state')->badge(),
                                                TextEntry::make('message'),
                                                TextEntry::make('created_at')->dateTime()->label('Waktu'),
                                            ]),
                                    ]),
                            ]),

                        Section::make('Catatan Internal')
                            ->columnSpan(1)
                            ->schema([
                                TextEntry::make('notes')
                                    ->label('Internal Notes')
                                    ->placeholder('Tidak ada catatan.')
                                    ->prose(),
                            ]),
                    ]),
            ]);
    }
}
