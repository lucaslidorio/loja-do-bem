<?php

namespace App\Http\Controllers;

use App\Enums\StatusLote;
use App\Models\Doador;
use App\Models\Edicao;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ResultadoController extends Controller
{
    public function show(Edicao $edicao): View
    {
        // Só aparece depois que a organização liga "Mostrar total arrecadado" na edição.
        // Quem está logado no painel vê antes, como prévia.
        $previa = ! $edicao->resultado_publico;
        abort_if($previa && ! Auth::check(), 404);

        $arrematados = $edicao->lotes()->where('status', StatusLote::Arrematado);

        $doadores = Doador::query()
            ->whereHas('lotes', fn ($q) => $q->where('edicao_id', $edicao->getKey()))
            ->orderBy('nome')
            ->pluck('nome')
            ->reject(fn ($nome) => in_array(Str::lower(Str::ascii($nome)), ['anonimo', 'anonima'], true))
            ->values();

        return view('loja.resultado', [
            'edicao' => $edicao,
            'previa' => $previa,
            'total' => $edicao->totalArrecadado(),
            'qtdArrematados' => (clone $arrematados)->count(),
            'totalLotes' => $edicao->lotes()->publicado()->count(),
            'maiores' => (clone $arrematados)->orderByDesc('valor_arrematado')->limit(5)->get()
                ->each(fn ($l) => $l->setRelation('edicao', $edicao)),
            'doadores' => $doadores,
        ]);
    }
}
