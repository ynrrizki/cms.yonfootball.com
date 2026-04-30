<?php

namespace App\Filament\Resources\Vouchers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class VouchersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->description(fn ($record) => $record->code)
                    ->sortable(),
                TextColumn::make('discount')
                    ->label('Potongan')
                    ->getStateUsing(fn ($record) => $record->price_flat ? "Rp " . number_format($record->price_flat) : ($record->price_percentage ? $record->price_percentage . "%" : "0")),
                TextColumn::make('usage')
                    ->label('Usage')
                    ->getStateUsing(fn ($record) => ($record->usage_count ?? 0) . " / " . ($record->usage_limit ?? '∞'))
                    ->badge()
                    ->color(fn ($record) => $record->usage_limit && $record->usage_count >= $record->usage_limit ? 'danger' : 'success'),
                TextColumn::make('status')
                    ->label('Masa Berlaku')
                    ->getStateUsing(fn ($record) => now()->gt($record->ended_date) ? 'EXPIRED' : ($record->is_active ? 'ACTIVE' : 'INACTIVE'))
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'EXPIRED' => 'danger',
                        'ACTIVE' => 'success',
                        'INACTIVE' => 'gray',
                    }),
                ToggleColumn::make('is_active')
                    ->label('Toggle Active'),
                TextColumn::make('ended_date')
                    ->label('Ends At')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
