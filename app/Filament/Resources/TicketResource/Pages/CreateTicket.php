<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Resources\TicketResource;
use App\Models\File;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;

class CreateTicket extends CreateRecord
{
    protected static string $resource = TicketResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * Handle file attachments after the ticket is created.
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
    }
}
