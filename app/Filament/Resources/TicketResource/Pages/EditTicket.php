<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Resources\TicketResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditTicket extends EditRecord
{
    protected static string $resource = TicketResource::class;

    protected string $view = 'filament.resources.ticket-resource.pages.edit-ticket';

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->label('Lihat Detail')
                ->icon('heroicon-o-eye'),

            Actions\DeleteAction::make()
                ->label('Hapus Tiket')
                ->visible(fn () => auth()->user()?->hasRole('admin')),
        ];
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->title('Perubahan Tiket Berhasil Disimpan')
            ->success()
            ->icon('heroicon-o-check-circle');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
