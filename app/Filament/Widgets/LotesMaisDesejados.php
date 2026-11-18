<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Lotes\LoteResource;
use App\Models\Edicao;
use App\Models\Lote;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LotesMaisDesejados extends TableWidget
{
    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Lotes mais desejados';

    public function table(Table $table): Table
    {
        return $table
            ->description('Ajuda a montar a ordem do leilão: guarde os mais procurados para o momento de maior público.')
            ->query(
                Lote::query()
                    ->where('edicao_id', Edicao::atual()?->getKey())
                    ->where('interesses_count', '>', 0)
                    ->orderByDesc('interesses_count')
                    ->limit(10)
            )
            ->paginated(false)
            ->columns([
                TextColumn::make('numero')->label('Lote')->weight('bold'),
                TextColumn::make('nome'),
                TextColumn::make('interesses_count')->label('Interesses')->alignEnd(),
            ])
            ->recordUrl(fn (Lote $record) => LoteResource::getUrl('edit', ['record' => $record]))
            ->emptyStateHeading('Ninguém marcou interesse ainda')
            ->emptyStateDescription('Assim que o link da loja circular, os lotes aparecem aqui.');
    }
}
