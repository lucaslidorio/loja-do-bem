<?php

namespace App\Filament\Resources\Lotes\Tables;

use App\Enums\StatusLote;
use App\Models\Edicao;
use App\Models\Lote;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class LotesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('numero')
            ->paginated([25, 50, 100, 'all'])
            ->defaultPaginationPageOption(50)
            ->modifyQueryUsing(fn ($query) => $query->with(['categoria', 'doador', 'edicao']))
            ->columns([
                ImageColumn::make('imagem')
                    ->label('')
                    ->state(fn (Lote $record) => $record->imagemUrl('thumb'))
                    ->imageSize(48)
                    ->square(),
                TextColumn::make('numero')
                    ->label('Lote')
                    ->sortable()
                    ->searchable()
                    ->weight('bold')
                    ->fontFamily('mono'),
                TextColumn::make('nome')
                    ->searchable()
                    ->wrap()
                    ->description(fn (Lote $record) => $record->doador?->nome),
                TextColumn::make('categoria.nome')
                    ->label('Categoria')
                    ->badge()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('valor_referencia')
                    ->label('Valor ref.')
                    ->state(fn (Lote $record) => $record->valor_imensuravel ? 'Imensurável' : $record->valor_referencia)
                    ->formatStateUsing(fn ($state) => is_numeric($state) ? Lote::reais($state) : $state)
                    ->alignEnd()
                    ->sortable(),
                IconColumn::make('destaque')
                    ->boolean()
                    ->trueIcon(Heroicon::Star)
                    ->falseIcon(Heroicon::OutlinedStar)
                    ->trueColor('warning')
                    ->falseColor('gray')
                    ->alignCenter(),
                TextColumn::make('interesses_count')
                    ->label('Interesses')
                    ->icon(Heroicon::Heart)
                    ->sortable()
                    ->alignEnd(),
                TextColumn::make('status')
                    ->badge()
                    ->description(fn (Lote $record) => $record->arrematado() ? Lote::reais($record->valor_arrematado) : null),
                ToggleColumn::make('publicado')
                    ->label('No site'),
            ])
            ->filters([
                SelectFilter::make('edicao_id')
                    ->label('Edição')
                    ->relationship('edicao', 'titulo')
                    ->default(fn () => Edicao::atual()?->getKey()),
                SelectFilter::make('categoria_id')
                    ->label('Categoria')
                    ->relationship('categoria', 'nome'),
                SelectFilter::make('doador_id')
                    ->label('Doador')
                    ->relationship('doador', 'nome')
                    ->searchable(),
                SelectFilter::make('status')
                    ->options(StatusLote::class),
                TernaryFilter::make('destaque'),
            ])
            ->recordActions([
                Action::make('arrematar')
                    ->label('Arrematado')
                    ->iconButton()
                    ->tooltip('Registrar arremate')
                    ->icon(Heroicon::OutlinedCheckBadge)
                    ->color('success')
                    ->visible(fn (Lote $record) => ! $record->arrematado())
                    ->modalHeading(fn (Lote $record) => "Lote {$record->numero} arrematado")
                    ->modalWidth('sm')
                    ->schema([
                        TextInput::make('valor_arrematado')
                            ->label('Valor do lance vencedor')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('R$')
                            ->required()
                            ->autofocus(),
                    ])
                    ->action(function (Lote $record, array $data) {
                        $record->update(['status' => StatusLote::Arrematado, ...$data]);
                        Notification::make()->title("Lote {$record->numero} registrado")->success()->send();
                    }),
                ActionGroup::make([
                    EditAction::make(),
                    Action::make('telao')
                        ->label('Mostrar no telão')
                        ->icon(Heroicon::OutlinedTv)
                        ->action(function (Lote $record) {
                            $record->edicao->update(['lote_atual_id' => $record->getKey()]);
                            Notification::make()->title("Lote {$record->numero} está no telão")->success()->send();
                        }),
                    Action::make('site')
                        ->label('Ver na loja')
                        ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                        ->url(fn (Lote $record) => $record->url())
                        ->openUrlInNewTab(),
                    Action::make('desfazer')
                        ->label('Voltar para disponível')
                        ->icon(Heroicon::OutlinedArrowUturnLeft)
                        ->visible(fn (Lote $record) => $record->arrematado())
                        ->requiresConfirmation()
                        ->action(fn (Lote $record) => $record->update(['status' => StatusLote::Disponivel])),
                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('etiquetas')
                        ->label('Imprimir etiquetas')
                        ->icon(Heroicon::OutlinedQrCode)
                        ->action(fn (Collection $records, $livewire) => $livewire->js(
                            'window.open('.json_encode(route('organizacao.etiquetas', ['ids' => $records->modelKeys() ? implode(',', $records->modelKeys()) : ''])).', "_blank")'
                        ))
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('publicar')
                        ->label('Publicar no site')
                        ->icon(Heroicon::OutlinedEye)
                        ->action(fn (Collection $records) => $records->each->update(['publicado' => true]))
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('ocultar')
                        ->label('Tirar do site')
                        ->icon(Heroicon::OutlinedEyeSlash)
                        ->action(fn (Collection $records) => $records->each->update(['publicado' => false]))
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
