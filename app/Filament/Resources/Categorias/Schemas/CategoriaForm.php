<?php

namespace App\Filament\Resources\Categorias\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CategoriaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('nome')
                ->required()
                ->maxLength(80)
                ->unique('categorias', 'nome', ignoreRecord: true),
            TextInput::make('ordem')
                ->numeric()
                ->default(0)
                ->helperText('Menor aparece primeiro nos filtros da loja.'),
        ]);
    }
}
