<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\Role;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm {
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identitas User')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Select::make('role')
                            ->options(Role::class)
                            ->required()
                            ->native(false),
                    ]),

                Section::make('Keamanan')
                    ->description('Kosongkan password jika tidak ingin mengubahnya saat edit')
                    ->schema([
                        TextInput::make('password')
                            ->password()
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->maxLength(255)
                            ->revealable(),
                    ]),
            ]);
    }
}
