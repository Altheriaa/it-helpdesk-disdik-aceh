<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Resources\TicketResource;
use App\Models\File;
use App\Models\Reply;
use App\Models\User;
use Filament\Actions;
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
            Actions\EditAction::make()
                ->visible(fn () => auth()->user()?->hasAnyRole(['admin', 'it_support'])),
        ];
    }
}
