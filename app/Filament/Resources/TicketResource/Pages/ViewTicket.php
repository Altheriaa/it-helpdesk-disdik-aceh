<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Resources\TicketResource;
use App\Models\File;
use App\Models\Reply;
use App\Models\Support;
use App\Models\User;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Livewire\WithFileUploads;

class ViewTicket extends ViewRecord
{
    use WithFileUploads;

    protected static string $resource = TicketResource::class;

    protected string $view = 'filament.resources.ticket-resource.pages.view-ticket';

    public string $replyMessage = '';

    public array $attachments = [];

    public function sendReply(): void
    {
        $this->validate([
            'replyMessage' => 'required|string|min:1',
        ]);

        $reply = Reply::create([
            'ticket_id' => $this->record->id,
            'user_id' => auth()->id(),
            'message' => $this->replyMessage,
        ]);

        foreach ($this->attachments as $file) {
            $path = $file->store('replies', 'public');
            File::create([
                'reply_id' => $reply->id,
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
            ]);
        }

        $this->replyMessage = '';
        $this->attachments = [];

        // Notify relevant user
        $this->sendReplyNotification();

        Notification::make()
            ->title('Pesan Terkirim')
            ->success()
            ->send();
    }

    protected function sendReplyNotification(): void
    {
        $currentUser = auth()->user();
        $ticket = $this->record;

        $recipients = collect();

        if ($currentUser->hasAnyRole(['admin', 'it_support'])) {
            // Notify client
            if ($ticket->client?->user) {
                $recipients->push($ticket->client->user);
            }
        } else {
            // Notify IT Support & Admin
            if ($ticket->support?->user) {
                $recipients->push($ticket->support->user);
            }
            $admins = User::whereHas('roles', fn ($q) => $q->where('name', 'admin'))->get();
            $recipients = $recipients->merge($admins)->unique('id');
        }

        $recipients = $recipients->reject(fn ($u) => $u->id === $currentUser->id);

        if ($recipients->count() > 0) {
            Notification::make()
                ->title('Balasan Baru pada Tiket #'.$ticket->id)
                ->body($currentUser->name.': '.str($this->replyMessage)->limit(50))
                ->icon('heroicon-o-chat-bubble-left-right')
                ->info()
                ->actions([
                    Actions\Action::make('view')
                        ->label('Lihat Balasan')
                        ->url(TicketResource::getUrl('view', ['record' => $ticket])),
                ])
                ->sendToDatabase($recipients);
        }
    }

    public function removeAttachment(int $index): void
    {
        if (isset($this->attachments[$index])) {
            unset($this->attachments[$index]);
            $this->attachments = array_values($this->attachments);
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('assignToMe')
                ->label('Assign ke Saya')
                ->icon('heroicon-o-user-check')
                ->color('success')
                ->visible(fn () => auth()->user()?->hasRole('it_support') && $this->record->support_id !== auth()->user()?->support?->id)
                ->action(function (): void {
                    $user = auth()->user();
                    $this->record->update([
                        'support_id' => $user->support?->id,
                        'status' => $this->record->status === 'open' ? 'in_progress' : $this->record->status,
                    ]);

                    Notification::make()
                        ->title('Tiket Berhasil Diassign')
                        ->body('Anda sekarang menangani Tiket #'.$this->record->id.'.')
                        ->success()
                        ->send();
                }),

            Actions\Action::make('assignSupport')
                ->label('Assign IT Support')
                ->icon('heroicon-o-user-plus')
                ->color('primary')
                ->visible(fn () => auth()->user()?->hasAnyRole(['admin', 'it_support']))
                ->form([
                    Forms\Components\Select::make('support_id')
                        ->label('Pilih Petugas IT Support')
                        ->options(Support::with('user')->get()->pluck('user.name', 'id'))
                        ->searchable()
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $this->record->update([
                        'support_id' => $data['support_id'],
                        'status' => $this->record->status === 'open' ? 'in_progress' : $this->record->status,
                    ]);

                    $support = Support::find($data['support_id']);
                    Notification::make()
                        ->title('IT Support Berhasil Ditetapkan')
                        ->body('Tiket #'.$this->record->id.' ditugaskan kepada '.($support->user->name ?? 'IT Support').'.')
                        ->success()
                        ->send();
                }),

            Actions\Action::make('changeStatus')
                ->label('Ubah Status')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->visible(fn () => auth()->user()?->hasAnyRole(['admin', 'it_support']))
                ->form([
                    Forms\Components\Select::make('status')
                        ->label('Pilih Status Tiket')
                        ->options([
                            'open' => 'Open (Baru)',
                            'in_progress' => 'In Progress (Diproses)',
                            'resolved' => 'Resolved (Selesai)',
                            'closed' => 'Closed (Ditutup)',
                            'cancelled' => 'Cancelled (Dibatalkan)',
                        ])
                        ->default(fn () => $this->record->status)
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $this->record->update([
                        'status' => $data['status'],
                    ]);

                    Notification::make()
                        ->title('Status Tiket Berhasil Diubah')
                        ->success()
                        ->send();
                }),

            Actions\EditAction::make()
                ->visible(fn () => auth()->user()?->hasRole('admin')),
        ];
    }
}
