<?php

namespace App\Filament\Resources\Audits\Tables;

use App\Enums\AuditType;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AuditsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->badge()
                    ->color(fn (AuditType $state): string => match ($state) {
                        AuditType::INSERT => 'success',
                        AuditType::UPDATE => 'warning',
                        AuditType::DELETE => 'danger',
                    })
                    ->searchable(),
                TextColumn::make('resource_type')
                    ->label('Target')
                    ->description(fn ($record) => "ID: " . $record->resource_id)
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options(AuditType::class),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->bulkActions([
                // Read-only list
            ]);
    }
}
