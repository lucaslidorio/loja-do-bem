<?php

namespace App\Filament\Resources\Doadores\Pages;

use App\Filament\Resources\Doadores\DoadorResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageDoadores extends ManageRecords
{
    protected static string $resource = DoadorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Novo doador'),
        ];
    }
}
