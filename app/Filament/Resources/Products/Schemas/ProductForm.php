<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Produk')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('code')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50),
                        TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Select::make('category_id')
                            ->relationship('category', 'name')
                            ->required(),
                    ]),

                Section::make('Media & Konten')
                    ->columns(2)
                    ->schema([
                        FileUpload::make('leading_url')
                            ->image()
                            ->imageEditor()
                            ->directory('products/leading')
                            ->label('Leading Image'),
                        FileUpload::make('background_url')
                            ->image()
                            ->imageEditor()
                            ->directory('products/background')
                            ->label('Background Image'),
                    ]),

                Section::make('Konfigurasi & Visibilitas')
                    ->columns(2)
                    ->schema([
                        KeyValue::make('inputs')
                            ->label('User Inputs (Form Fields)')
                            ->keyLabel('Field ID')
                            ->valueLabel('Field Name')
                            ->columnSpanFull(),
                        Toggle::make('is_active')
                            ->required()
                            ->default(true),
                        Toggle::make('is_popular')
                            ->required()
                            ->default(false),
                    ]),
            ]);
    }
}
