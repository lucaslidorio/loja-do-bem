<?php

namespace App\Filament\Resources\Doadores\Schemas;

use App\Filament\Support\CampoImagem;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class DoadorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('nome')
                    ->label('Nome (empresa ou pessoa)')
                    ->required()
                    ->maxLength(150)
                    ->columnSpanFull(),
                TextInput::make('cidade'),
                TextInput::make('instagram')->prefix('@'),
                TextInput::make('whatsapp')->tel(),
                TextInput::make('site')->url(),
                CampoImagem::make('logo_path', 'doadores', comOg: false)
                    ->label('Logo')
                    ->columnSpanFull(),
            ]);
    }
}
