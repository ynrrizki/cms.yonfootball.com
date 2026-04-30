<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Enums\OrderStatus;
use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Pages\ViewRecord;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('process')
                ->label('Tandai Processing')
                ->icon('heroicon-o-arrow-path')
                ->color('info')
                ->visible(fn (Order $record): bool => $record->status === OrderStatus::PENDING && $record->transaction?->status === 'PAID')
                ->action(function (Order $record) {
                    $record->update([
                        'status' => OrderStatus::PROCESSING,
                        'processed_by' => auth()->id(),
                    ]);
                    $record->histories()->create([
                        'state' => OrderStatus::PROCESSING,
                        'message' => 'Pesanan mulai diproses oleh admin.',
                        'users_id' => auth()->id(),
                        'created_at' => now(),
                    ]);
                })
                ->requiresConfirmation(),
            Action::make('success')
                ->label('Tandai Success')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn (Order $record): bool => $record->status === OrderStatus::PROCESSING)
                ->form([
                    Textarea::make('notes')
                        ->label('Catatan')
                        ->required(),
                ])
                ->action(function (Order $record, array $data) {
                    $record->update([
                        'status' => OrderStatus::SUCCESS,
                        'completed_at' => now(),
                        'notes' => $data['notes'],
                    ]);
                    $record->histories()->create([
                        'state' => OrderStatus::SUCCESS,
                        'message' => 'Pesanan telah selesai.',
                        'users_id' => auth()->id(),
                        'created_at' => now(),
                    ]);
                })
                ->requiresConfirmation(),
            Action::make('addNote')
                ->label('Tambah Catatan')
                ->icon('heroicon-o-chat-bubble-left-ellipsis')
                ->color('gray')
                ->form([
                    Textarea::make('note')
                        ->label('Catatan Baru')
                        ->required(),
                ])
                ->action(function (Order $record, array $data) {
                    $newNote = "[" . now()->format('Y-m-d H:i') . "] " . auth()->user()->name . ": " . $data['note'];
                    $record->update([
                        'notes' => $record->notes ? $record->notes . "\n" . $newNote : $newNote,
                    ]);
                }),
        ];
    }
}
