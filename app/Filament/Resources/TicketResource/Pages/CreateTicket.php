<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Resources\TicketResource;
use App\Models\File;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;

class CreateTicket extends CreateRecord
{
    protected static string $resource = TicketResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->title('Tiket Berhasil Dibuat!')
            ->body('Tiket Anda (#'.$this->record->id.') telah berhasil terkirim dan siap ditangani tim IT Support.')
            ->success()
            ->icon('heroicon-o-check-circle');
    }

    /**
     * Handle file attachments and send real-time database notifications after creation.
     */
    protected function afterCreate(): void
    {
        $attachments = $this->data['attachments'] ?? [];

        foreach ($attachments as $path) {
            File::create([
                'ticket_id' => $this->record->id,
                'file_path' => $path,
                'file_name' => basename($path),
                'file_size' => Storage::disk('public')->size($path),
            ]);
        }

        // Send real-time database notifications to ALL Admins & IT Support
        $recipients = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['admin', 'it_support']);
        })->get();

        if ($recipients->count() > 0) {
            $clientName = $this->record->client->user->name ?? 'Pegawai';

            Notification::make()
                ->title('Tiket Baru Masuk!')
                ->body('Tiket #'.$this->record->id.' dari '.$clientName.': '.$this->record->subject)
                ->icon('heroicon-o-ticket')
                ->warning()
                ->actions([
                    Action::make('view')
                        ->label('Buka Tiket')
                        ->url(TicketResource::getUrl('view', ['record' => $this->record])),
                ])
                ->sendToDatabase($recipients);
        }
    }
}
