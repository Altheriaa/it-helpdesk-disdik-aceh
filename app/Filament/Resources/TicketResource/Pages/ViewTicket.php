<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Resources\TicketResource;
use App\Models\Reply;
use Filament\Actions;
use Filament\Forms;
use Filament\Infolists;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ViewTicket extends ViewRecord
{
    protected static string $resource = TicketResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Detail Tiket')
                    ->schema([
                        Infolists\Components\TextEntry::make('id')
                            ->label('No. Tiket')
                            ->prefix('#'),

                        Infolists\Components\TextEntry::make('client.user.name')
                            ->label('Pegawai'),

                        Infolists\Components\TextEntry::make('client.division.name')
                            ->label('Bidang'),

                        Infolists\Components\TextEntry::make('subject')
                            ->label('Subjek')
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('description')
                            ->label('Deskripsi')
                            ->columnSpanFull()
                            ->markdown(),

                        Infolists\Components\TextEntry::make('priority')
                            ->label('Prioritas')
                            ->badge()
                            ->formatStateUsing(fn (string $state) => match ($state) {
                                'low' => 'Rendah',
                                'medium' => 'Sedang',
                                'high' => 'Tinggi',
                                'critical' => 'Kritis',
                                default => $state,
                            })
                            ->color(fn (string $state) => match ($state) {
                                'low' => 'gray',
                                'medium' => 'warning',
                                'high' => 'danger',
                                'critical' => 'primary',
                                default => 'gray',
                            }),

                        Infolists\Components\TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->formatStateUsing(fn (string $state) => match ($state) {
                                'open' => 'Open',
                                'in_progress' => 'In Progress',
                                'resolved' => 'Resolved',
                                'closed' => 'Closed',
                                default => $state,
                            })
                            ->color(fn (string $state) => match ($state) {
                                'open' => 'gray',
                                'in_progress' => 'warning',
                                'resolved' => 'success',
                                'closed' => 'danger',
                                default => 'gray',
                            }),

                        Infolists\Components\TextEntry::make('support.user.name')
                            ->label('IT Support')
                            ->default('Belum diassign'),

                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Dibuat')
                            ->dateTime('d M Y H:i'),
                    ])->columns(3),

                Section::make('Lampiran')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('files')
                            ->schema([
                                Infolists\Components\TextEntry::make('file_name')
                                    ->label('Nama File')
                                    ->url(fn ($record) => asset('storage/'.$record->file_path), shouldOpenInNewTab: true),
                            ])
                            ->columns(1),
                    ])
                    ->visible(fn ($record) => $record->files->count() > 0)
                    ->collapsible(),

                Section::make('Thread Balasan')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('replies')
                            ->schema([
                                Infolists\Components\TextEntry::make('user.name')
                                    ->label('Dari')
                                    ->weight('bold'),

                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('Waktu')
                                    ->dateTime('d M Y H:i')
                                    ->size('sm'),

                                Infolists\Components\TextEntry::make('message')
                                    ->label('')
                                    ->markdown()
                                    ->columnSpanFull(),
                            ])
                            ->columns(2),
                    ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn () => auth()->user()?->hasAnyRole(['admin', 'it_support'])),

            Actions\Action::make('reply')
                ->label('Balas Tiket')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->color('success')
                ->form([
                    Forms\Components\Textarea::make('message')
                        ->label('Pesan Balasan')
                        ->required()
                        ->rows(4),
                ])
                ->action(function (array $data): void {
                    Reply::create([
                        'ticket_id' => $this->record->id,
                        'user_id' => auth()->id(),
                        'message' => $data['message'],
                    ]);

                    Notification::make()
                        ->title('Balasan terkirim')
                        ->success()
                        ->send();

                    $this->refreshFormData(['replies']);
                })
                ->visible(fn () => ! in_array($this->record->status, ['resolved', 'closed'])),
        ];
    }
}
