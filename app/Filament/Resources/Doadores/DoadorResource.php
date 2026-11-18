<?php

namespace App\Filament\Resources\Doadores;

use App\Filament\Resources\Doadores\Pages\ManageDoadores;
use App\Filament\Resources\Doadores\Schemas\DoadorForm;
use App\Models\Doador;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class DoadorResource extends Resource
{
    protected static ?string $model = Doador::class;

    protected static ?string $slug = 'doadores';

    protected static ?string $modelLabel = 'doador';

    protected static ?string $pluralModelLabel = 'doadores';

    protected static ?string $recordTitleAttribute = 'nome';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedBuildingStorefront;

    protected static string | UnitEnum | null $navigationGroup = 'Cadastros';

    public static function form(Schema $schema): Schema
    {
        return DoadorForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('nome')
            ->columns([
                ImageColumn::make('logo')
                    ->label('')
                    ->state(fn (Doador $record) => $record->logoUrl())
                    ->imageSize(40)
                    ->circular(),
                TextColumn::make('nome')->searchable()->weight('medium'),
                TextColumn::make('cidade')->toggleable(),
                TextColumn::make('instagram')->prefix('@')->toggleable(),
                TextColumn::make('lotes_count')->label('Lotes doados')->counts('lotes')->alignEnd()->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageDoadores::route('/'),
        ];
    }
}
