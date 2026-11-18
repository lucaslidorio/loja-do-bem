<?php

namespace App\Filament\Resources\Lotes\Pages;

use App\Filament\Resources\Lotes\LoteResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLote extends CreateRecord
{
    protected static string $resource = LoteResource::class;

    // Depois de salvar, volta para um formulário novo: agiliza cadastrar vários lotes seguidos
    protected static bool $canCreateAnother = true;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
