<?php

namespace App\Filament\Widgets;

use App\Enums\StatusLote;
use App\Models\Edicao;
use App\Models\Lote;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ResumoEdicao extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $pollingInterval = '30s';

    protected function getHeading(): ?string
    {
        return Edicao::atual()?->titulo ?? 'Nenhuma edição cadastrada';
    }

    protected function getStats(): array
    {
        $edicao = Edicao::atual();

        if (! $edicao) {
            return [
                Stat::make('Comece por aqui', 'Cadastre a edição')
                    ->description('Menu Leilão → Edições')
                    ->url(route('filament.admin.resources.edicoes.create')),
            ];
        }

        $lotes = $edicao->lotes();
        $total = (clone $lotes)->count();
        $arrematados = (clone $lotes)->where('status', StatusLote::Arrematado)->count();
        $semImagem = (clone $lotes)->whereNull('imagem_path')->whereNull('arte_path')->count();
        $interesses = (int) (clone $lotes)->sum('interesses_count');
        $arrecadado = $edicao->totalArrecadado();

        $dias = (int) now()->startOfDay()->diffInDays($edicao->data_evento->copy()->startOfDay(), false);

        return [
            Stat::make('Lotes cadastrados', $total)
                ->description($semImagem ? "{$semImagem} sem imagem" : 'Todos com imagem')
                ->descriptionIcon($semImagem ? Heroicon::ExclamationTriangle : Heroicon::CheckCircle)
                ->color($semImagem ? 'warning' : 'success'),
            Stat::make('Interesses marcados', $interesses)
                ->description('Cliques em "tenho interesse" na loja')
                ->descriptionIcon(Heroicon::Heart),
            Stat::make('Arrecadado', Lote::reais($arrecadado))
                ->description("{$arrematados} de {$total} lotes arrematados")
                ->color('success'),
            Stat::make('Leilão', $edicao->data_evento->format('d/m'))
                ->description(match (true) {
                    $dias > 1 => "Faltam {$dias} dias",
                    $dias === 1 => 'É amanhã',
                    $dias === 0 => 'É hoje',
                    default => 'Já aconteceu',
                }),
        ];
    }
}
