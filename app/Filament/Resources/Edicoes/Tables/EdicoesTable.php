<?php

namespace App\Filament\Resources\Edicoes\Tables;

use App\Models\Edicao;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EdicoesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('data_evento', 'desc')
            ->columns([
                TextColumn::make('titulo')->label('Edição')->searchable()->weight('medium'),
                TextColumn::make('data_evento')->label('Data')->dateTime('d/m/Y H:i')->sortable(),
                TextColumn::make('cidade')->formatStateUsing(fn (Edicao $record) => "{$record->cidade} – {$record->uf}"),
                TextColumn::make('lotes_count')->label('Lotes')->counts('lotes')->alignEnd(),
                IconColumn::make('ativa')->label('Atual')->boolean(),
            ])
            ->recordActions([
                Action::make('site')
                    ->label('Ver no site')
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->color('gray')
                    ->url(fn (Edicao $record) => route('edicao', $record))
                    ->openUrlInNewTab(),
                EditAction::make(),
            ]);
    }
}
