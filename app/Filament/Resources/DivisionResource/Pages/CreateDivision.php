<?php

namespace App\Filament\Resources\DivisionResource\Pages;

use App\Filament\Resources\DivisionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDivision extends CreateRecord
{
    protected static string $resource = DivisionResource::class;

    protected string $view = 'filament.resources.division-resource.pages.create-division';

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
