<?php

namespace App\Filament\Resources\Lotes\Schemas;

use App\Enums\StatusLote;
use App\Filament\Resources\Categorias\Schemas\CategoriaForm;
use App\Filament\Resources\Doadores\Schemas\DoadorForm;
use App\Filament\Support\CampoImagem;
use App\Models\Edicao;
use App\Models\Lote;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Unique;

class LoteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Lote')
                    ->columnSpan(2)
                    ->columns(4)
                    ->schema([
                        Select::make('edicao_id')
                            ->label('Edição')
                            ->relationship('edicao', 'titulo')
                            ->default(fn () => Edicao::atual()?->getKey())
                            ->required()
                            ->live()
                            ->columnSpan(3),
                        TextInput::make('numero')
                            ->label('Nº do lote')
                            ->numeric()
                            ->minValue(1)
                            ->required()
                            ->default(fn () => self::proximoNumero(Edicao::atual()?->getKey()))
                            ->unique(
                                ignoreRecord: true,
                                modifyRuleUsing: fn (Unique $rule, Get $get) => $rule->where('edicao_id', $get('edicao_id')),
                            )
                            ->validationMessages(['unique' => 'Já existe um lote com esse número nesta edição.']),
                        TextInput::make('nome')
                            ->label('Nome do produto')
                            ->placeholder('Caixa térmica 28L Soprano')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Select::make('categoria_id')
                            ->label('Categoria')
                            ->relationship('categoria', 'nome', fn ($query) => $query->orderBy('ordem')->orderBy('nome'))
                            ->searchable()
                            ->preload()
                            ->createOptionForm(fn (Schema $schema) => CategoriaForm::configure($schema))
                            ->columnSpan(2),
                        Select::make('doador_id')
                            ->label('Doador')
                            ->relationship('doador', 'nome')
                            ->searchable()
                            ->preload()
                            ->createOptionForm(fn (Schema $schema) => DoadorForm::configure($schema))
                            ->columnSpan(2),
                        Textarea::make('descricao')
                            ->label('Descrição')
                            ->placeholder('Detalhes que ajudam a valorizar o lote: marca, estado, tamanho, história da peça...')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),

                Section::make('Valor e vitrine')
                    ->columnSpan(1)
                    ->schema([
                        Toggle::make('valor_imensuravel')
                            ->label('Valor imensurável')
                            ->helperText('Para peças autografadas e itens únicos.')
                            ->live(),
                        TextInput::make('valor_referencia')
                            ->label('Valor de referência')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('R$')
                            ->hidden(fn (Get $get) => (bool) $get('valor_imensuravel')),
                        Toggle::make('destaque')
                            ->label('Destaque')
                            ->helperText('Aparece no topo da loja.'),
                        Toggle::make('publicado')
                            ->label('Publicado no site')
                            ->default(true),
                    ]),

                Section::make('Imagens')
                    ->columnSpan(2)
                    ->columns(2)
                    ->description('Envie a foto do jeito que estiver: o sistema reduz e converte para WebP automaticamente.')
                    ->schema([
                        CampoImagem::make('imagem_path', 'lotes')
                            ->label('Foto do produto')
                            ->helperText('Aparece na vitrine. Se ficar vazia, usamos a arte.'),
                        CampoImagem::make('arte_path', 'artes')
                            ->label('Arte de divulgação')
                            ->helperText('O card pronto. Vira a prévia do link no WhatsApp.'),
                    ]),

                Section::make('Resultado do leilão')
                    ->columnSpan(1)
                    ->schema([
                        Select::make('status')
                            ->options(StatusLote::class)
                            ->default(StatusLote::Disponivel)
                            ->required()
                            ->live(),
                        TextInput::make('valor_arrematado')
                            ->label('Arrematado por')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('R$')
                            ->required(fn (Get $get) => self::ehArrematado($get('status')))
                            ->visible(fn (Get $get) => self::ehArrematado($get('status'))),
                    ]),
            ]);
    }

    public static function proximoNumero(?int $edicaoId): int
    {
        if (! $edicaoId) {
            return 101;
        }

        return ((int) Lote::where('edicao_id', $edicaoId)->max('numero') ?: 100) + 1;
    }

    private static function ehArrematado(mixed $status): bool
    {
        return ($status instanceof StatusLote ? $status : StatusLote::tryFrom((string) $status)) === StatusLote::Arrematado;
    }
}
