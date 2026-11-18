<?php

namespace App\Filament\Resources\Edicoes\Pages;

use App\Filament\Resources\Edicoes\EdicaoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEdicoes extends ListRecords
{
    protected static string $resource = EdicaoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Nova edição'),
        ];
    }
}
