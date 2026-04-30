<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderStateHistory;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('ticket_number')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable(),
                TextColumn::make('variant.name')
                    ->label('Product Variant')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (OrderStatus $state): string => match ($state) {
                        OrderStatus::PENDING => 'warning',
                        OrderStatus::PROCESSING => 'info',
                        OrderStatus::COMPLETED => 'success',
                        OrderStatus::FAILED => 'danger',
                    }),
                TextColumn::make('processedBy.name')
                    ->label('Admin')
                    ->placeholder('System'),
                TextColumn::make('created_at')
                    ->label('Waktu Pesan')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(OrderStatus::class),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    Action::make('process')
                        ->label('Proses Pesanan')
                        ->icon('heroicon-o-arrow-path')
                        ->color('info')
                        ->visible(fn (Order $record): bool => 
                            $record->status === OrderStatus::PENDING && 
                            $record->transaction?->status === 'PAID'
                        )
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
                    Action::make('complete')
                        ->label('Selesaikan')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn (Order $record): bool => $record->status === OrderStatus::PROCESSING)
                        ->action(function (Order $record) {
                            $record->update([
                                'status' => OrderStatus::COMPLETED,
                                'completed_at' => now(),
                            ]);
                            $record->histories()->create([
                                'state' => OrderStatus::COMPLETED,
                                'message' => 'Pesanan telah diselesaikan.',
                                'users_id' => auth()->id(),
                                'created_at' => now(),
                            ]);
                        })
                        ->requiresConfirmation(),
                    Action::make('fail')
                        ->label('Gagalkan')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(fn (Order $record): bool => in_array($record->status, [OrderStatus::PENDING, OrderStatus::PROCESSING]))
                        ->form([
                            \Filament\Forms\Components\Textarea::make('message')
                                ->label('Alasan Kegagalan')
                                ->required(),
                        ])
                        ->action(function (Order $record, array $data) {
                            $record->update([
                                'status' => OrderStatus::FAILED,
                                'notes' => $data['message'],
                            ]);
                            $record->histories()->create([
                                'state' => OrderStatus::FAILED,
                                'message' => 'Pesanan gagal: ' . $data['message'],
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
                            \Filament\Forms\Components\Textarea::make('note')
                                ->label('Catatan Baru')
                                ->required(),
                        ])
                        ->action(function (Order $record, array $data) {
                            $newNote = "[" . now()->format('Y-m-d H:i') . "] " . auth()->user()->name . ": " . $data['note'];
                            $record->update([
                                'notes' => $record->notes ? $record->notes . "\n" . $newNote : $newNote,
                            ]);
                        }),
                ]),
            ])
            ->toolbarActions([
                // Read-only list
            ]);
    }
}
