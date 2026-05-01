<?php

namespace App\Filament\Resources\Vouchers\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class VoucherForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Voucher')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('code')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50)
                            ->uppercase(),
                    ]),

                Section::make('Konfigurasi Potongan')
                    ->columns(2)
                    ->schema([
                        TextInput::make('price_flat')
                            ->numeric()
                            ->prefix('Rp')
                            ->label('Flat Discount'),
                        TextInput::make('price_percentage')
                            ->numeric()
                            ->suffix('%')
                            ->label('Percentage Discount'),
                    ]),

                Section::make('Masa Berlaku & Limit')
                    ->columns(2)
                    ->schema([
                        DateTimePicker::make('effective_date')
                            ->label('Tanggal Mulai'),
                        DateTimePicker::make('ended_date')
                            ->label('Tanggal Berakhir')
                            ->after('effective_date'),
                        TextInput::make('usage_limit')
                            ->numeric()
                            ->label('Limit Pemakaian')
                            ->helperText('Kosongkan untuk tidak ada limit'),
                        TextInput::make('usage_count')
                            ->numeric()
                            ->disabled()
                            ->label('Total Terpakai')
                            ->default(0),
                    ]),

                Section::make('Status')
                    ->schema([
                        Toggle::make('is_active')
                            ->required()
                            ->default(true)
                            ->label('Aktifkan Voucher'),
                    ]),
            ]);
    }
}
