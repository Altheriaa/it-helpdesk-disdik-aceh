<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\Client;
use App\Models\Support;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * After creating the user, create the associated client or support profile.
     */
    protected function afterCreate(): void
    {
        $user = $this->record;
        $divisionId = $this->data['division_id'] ?? null;
        $position = $this->data['position'] ?? null;

        if ($user->hasRole('pegawai') && $divisionId) {
            Client::create([
                'user_id' => $user->id,
                'division_id' => $divisionId,
                'position' => $position,
            ]);
        }

        if ($user->hasRole('it_support')) {
            Support::create([
                'user_id' => $user->id,
                'division_id' => $divisionId,
                'position' => $position,
            ]);
        }
    }
}
