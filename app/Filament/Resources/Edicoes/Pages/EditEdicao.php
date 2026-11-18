<?php

namespace App\Filament\Resources\Edicoes\Pages;

use App\Filament\Resources\Edicoes\EdicaoResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEdicao extends EditRecord
{
    protected static string $resource = EdicaoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->modalDescription('Todos os lotes desta edição também serão apagados. Essa ação não pode ser desfeita.'),
        ];
    }
}
