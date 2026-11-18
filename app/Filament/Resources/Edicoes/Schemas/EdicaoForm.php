<?php

namespace App\Filament\Resources\Edicoes\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class EdicaoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Evento')
                    ->columnSpan(2)
                    ->columns(2)
                    ->schema([
                        TextInput::make('numero')
                            ->label('Número da edição')
                            ->numeric()
                            ->minValue(1)
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                if (blank($get('titulo')) && filled($state)) {
                                    $set('titulo', "{$state}º Leilão Direito de Viver");
                                }
                            }),
                        DateTimePicker::make('data_evento')
                            ->label('Data e hora')
                            ->seconds(false)
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                if (blank($get('slug')) && filled($state)) {
                                    $set('slug', substr((string) $state, 0, 4));
                                }
                            }),
                        TextInput::make('titulo')
                            ->label('Título')
                            ->placeholder('16º Leilão Direito de Viver')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        TextInput::make('slug')
                            ->label('Endereço no site')
                            ->prefix(url('/').'/')
                            ->helperText('O ano da edição. Ex.: 2026')
                            ->required()
                            ->regex('/^[0-9][a-z0-9-]*$/')
                            ->validationMessages(['regex' => 'Use o ano (ex.: 2026), só números, letras minúsculas e hífen.'])
                            ->unique(ignoreRecord: true),
                        TextInput::make('meta_arrecadacao')
                            ->label('Meta de arrecadação')
                            ->numeric()
                            ->prefix('R$'),
                        TextInput::make('cidade')->required()->default('Rolim de Moura'),
                        TextInput::make('uf')->label('UF')->required()->length(2)->default('RO')
                            ->dehydrateStateUsing(fn ($state) => strtoupper((string) $state)),
                    ]),

                Section::make('No site')
                    ->columnSpan(1)
                    ->schema([
                        Toggle::make('ativa')
                            ->label('Edição atual')
                            ->helperText('É a que aparece na página inicial. Ao ativar, as outras são desativadas.'),
                        Toggle::make('resultado_publico')
                            ->label('Mostrar total arrecadado')
                            ->helperText('Ligue depois do leilão para exibir o resultado no site.'),
                    ]),

                Section::make('Local e contato')
                    ->columnSpan(2)
                    ->columns(2)
                    ->schema([
                        TextInput::make('local_nome')->label('Local')->placeholder('Espaço Direito de Viver'),
                        TextInput::make('local_endereco')->label('Endereço'),
                        TextInput::make('maps_url')->label('Link do Google Maps')->url()->columnSpanFull(),
                        TextInput::make('whatsapp')
                            ->label('WhatsApp da organização')
                            ->tel()
                            ->placeholder('(69) 99999-9999')
                            ->dehydrateStateUsing(function ($state) {
                                $numeros = preg_replace('/\D/', '', (string) $state);

                                return $numeros === '' ? null : (strlen($numeros) <= 11 ? '55'.$numeros : $numeros);
                            }),
                        TextInput::make('instagram')->label('Instagram')->prefix('@')
                            ->dehydrateStateUsing(fn ($state) => filled($state) ? ltrim(trim($state), '@') : null),
                        Textarea::make('convite_info')
                            ->label('Convites')
                            ->placeholder('Convite R$ 50, com almoço. À venda em...')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),

                Section::make('Sobre a causa')
                    ->columnSpan(2)
                    ->schema([
                        Textarea::make('sobre')
                            ->hiddenLabel()
                            ->rows(5)
                            ->helperText('Texto curto que aparece no site, contando para onde vai o dinheiro.'),
                    ]),
            ]);
    }
}
