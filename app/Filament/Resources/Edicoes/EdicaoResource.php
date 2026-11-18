<?php

namespace App\Filament\Resources\Edicoes;

use App\Filament\Resources\Edicoes\Pages\CreateEdicao;
use App\Filament\Resources\Edicoes\Pages\EditEdicao;
use App\Filament\Resources\Edicoes\Pages\ListEdicoes;
use App\Filament\Resources\Edicoes\Schemas\EdicaoForm;
use App\Filament\Resources\Edicoes\Tables\EdicoesTable;
use App\Models\Edicao;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class EdicaoResource extends Resource
{
    protected static ?string $model = Edicao::class;

    protected static ?string $slug = 'edicoes';

    protected static ?string $modelLabel = 'edição';

    protected static ?string $pluralModelLabel = 'edições';

    protected static ?string $recordTitleAttribute = 'titulo';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static string | UnitEnum | null $navigationGroup = 'Leilão';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return EdicaoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EdicoesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEdicoes::route('/'),
            'create' => CreateEdicao::route('/create'),
            'edit' => EditEdicao::route('/{record}/edit'),
        ];
    }
}
