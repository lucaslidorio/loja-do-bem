<?php

namespace App\Filament\Resources\Lotes\Pages;

use App\Filament\Resources\Lotes\LoteResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Support\Icons\Heroicon;
use Filament\Resources\Pages\ListRecords;

class ListLotes extends ListRecords
{
    protected static string $resource = LoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('etiquetas')
                ->label('Etiquetas com QR Code')
                ->icon(Heroicon::OutlinedQrCode)
                ->color('gray')
                ->url(route('organizacao.etiquetas'), shouldOpenInNewTab: true),
            CreateAction::make()->label('Novo lote'),
        ];
    }
}
