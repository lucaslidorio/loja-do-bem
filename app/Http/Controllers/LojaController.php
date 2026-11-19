<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Doador;
use App\Models\Edicao;
use App\Models\Interesse;
use App\Models\Lote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LojaController extends Controller
{
    public function inicio(Request $request): View|\Illuminate\Http\Response
    {
        $edicao = Edicao::atual();

        if (! $edicao) {
            return response()->view('loja.sem-edicao', status: 503);
        }

        return $this->vitrine($request, $edicao);
    }

    public function edicao(Request $request, Edicao $edicao): View|RedirectResponse
    {
        if ($edicao->ativa) {
            return redirect()->route('inicio');
        }

        return $this->vitrine($request, $edicao);
    }

    public function lote(Request $request, Edicao $edicao, string $referencia): View|RedirectResponse
    {
        $numero = (int) strtok($referencia, '-');

        $lote = $edicao->lotes()
            ->publicado()
            ->where('numero', $numero)
            ->with(['categoria', 'doador'])
            ->firstOrFail();

        // URL antiga ou slug digitado errado: manda para o endereço certo
        if ($referencia !== $lote->referencia()) {
            return redirect($lote->url(), 301);
        }

        $lote->setRelation('edicao', $edicao);

        $vizinhos = $edicao->lotes()->publicado()
            ->whereKeyNot($lote->getKey())
            ->when($lote->categoria_id, fn ($q) => $q->orderByRaw('categoria_id = ? desc', [$lote->categoria_id]))
            ->orderByDesc('destaque')
            ->orderBy('numero')
            ->limit(4)
            ->get();

        $anterior = $edicao->lotes()->publicado()->where('numero', '<', $lote->numero)->orderByDesc('numero')->first(['id', 'numero', 'slug', 'nome']);
        $proximo = $edicao->lotes()->publicado()->where('numero', '>', $lote->numero)->orderBy('numero')->first(['id', 'numero', 'slug', 'nome']);

        return view('loja.lote', [
            'edicao' => $edicao,
            'lote' => $lote,
            'vizinhos' => $vizinhos,
            'anterior' => $anterior?->setRelation('edicao', $edicao),
            'proximo' => $proximo?->setRelation('edicao', $edicao),
            'interessado' => $this->interessesDoVisitante($request, collect([$lote->getKey()]))->isNotEmpty(),
        ]);
    }

    public function doadores(Edicao $edicao): View
    {
        $doadores = Doador::query()
            ->whereHas('lotes', fn ($q) => $q->where('edicao_id', $edicao->getKey())->publicado())
            ->with(['lotes' => fn ($q) => $q->where('edicao_id', $edicao->getKey())->publicado()->orderBy('numero')])
            ->orderBy('nome')
            ->get();

        return view('loja.doadores', compact('edicao', 'doadores'));
    }

    /**
     * Marca/desmarca "tenho interesse". Responde JSON para o botão do coração.
     */
    public function interesse(Request $request, Lote $lote): JsonResponse
    {
        abort_unless($lote->publicado, 404);

        $visitante = $request->attributes->get('visitante');

        $interessado = DB::transaction(function () use ($lote, $visitante) {
            $existente = Interesse::where('lote_id', $lote->getKey())->where('visitante', $visitante)->first();

            if ($existente) {
                $existente->delete();
                $lote->newQuery()->whereKey($lote->getKey())->where('interesses_count', '>', 0)->decrement('interesses_count');

                return false;
            }

            Interesse::create(['lote_id' => $lote->getKey(), 'visitante' => $visitante]);
            $lote->newQuery()->whereKey($lote->getKey())->increment('interesses_count');

            return true;
        });

        return response()->json([
            'interessado' => $interessado,
            'total' => (int) $lote->newQuery()->whereKey($lote->getKey())->value('interesses_count'),
        ]);
    }

    private function vitrine(Request $request, Edicao $edicao): View
    {
        $lotes = $edicao->lotes()
            ->publicado()
            ->with(['categoria', 'doador'])
            ->orderBy('numero')
            ->get()
            ->each(fn (Lote $lote) => $lote->setRelation('edicao', $edicao));

        $categorias = Categoria::query()
            ->whereIn('id', $lotes->pluck('categoria_id')->filter()->unique())
            ->orderBy('ordem')
            ->orderBy('nome')
            ->get();

        return view('loja.vitrine', [
            'edicao' => $edicao,
            'lotes' => $lotes,
            'destaques' => $lotes->where('destaque', true)->values(),
            'categorias' => $categorias,
            'meusInteresses' => $this->interessesDoVisitante($request, $lotes->modelKeys())->all(),
            'totalDoadores' => $lotes->pluck('doador_id')->filter()->unique()->count(),
            'arrecadado' => $edicao->resultado_publico ? $edicao->totalArrecadado() : null,
        ]);
    }

    private function interessesDoVisitante(Request $request, iterable $loteIds)
    {
        return Interesse::query()
            ->where('visitante', $request->attributes->get('visitante'))
            ->whereIn('lote_id', collect($loteIds))
            ->pluck('lote_id');
    }
}
