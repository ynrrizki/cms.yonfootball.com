<?php

namespace App\Filament\Resources\Audits\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Filament\Schemas\Schema;

class AuditInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Audit Details')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('user.name')->label('User'),
                        TextEntry::make('type')->badge(),
                        TextEntry::make('resource_type')->label('Resource'),
                        TextEntry::make('resource_id')->label('Resource ID'),
                        TextEntry::make('user_agent')->columnSpanFull(),
                    ]),
                Section::make('Resource Snapshot')
                    ->schema([
                        TextEntry::make('resource_snapshot')
                            ->label('Data Snapshot (JSON)')
                            ->json()
                            ->collapsible()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
