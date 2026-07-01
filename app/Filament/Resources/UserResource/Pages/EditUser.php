<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\Client;
use App\Models\Support;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * After saving the user, sync the associated client or support profile.
     */
    protected function afterSave(): void
    {
        $user = $this->record;
        $divisionId = $this->data['division_id'] ?? null;
        $position = $this->data['position'] ?? null;

        if ($user->hasRole('pegawai') && $divisionId) {
            Client::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'division_id' => $divisionId,
                    'position' => $position,
                ]
            );
        } elseif (! $user->hasRole('pegawai')) {
            Client::where('user_id', $user->id)->delete();
        }

        if ($user->hasRole('it_support')) {
            Support::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'division_id' => $divisionId,
                    'position' => $position,
                ]
            );
        } elseif (! $user->hasRole('it_support')) {
            Support::where('user_id', $user->id)->delete();
        }
    }
}
