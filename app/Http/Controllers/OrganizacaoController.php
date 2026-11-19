<?php

namespace App\Http\Controllers;

use App\Enums\StatusLote;
use App\Models\Edicao;
use App\Models\Lote;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Páginas de uso da organização (exigem login do painel):
 * folha de etiquetas com QR Code e controle do telão.
 */
class OrganizacaoController extends Controller
{
    public function etiquetas(Request $request): View
    {
        $edicao = $this->edicao($request);

        $lotes = $edicao->lotes()
            ->with('doador')
            ->when($request->filled('ids'), fn ($q) => $q->whereIn('id', array_filter(explode(',', $request->string('ids')))))
            ->when(! $request->filled('ids'), fn ($q) => $q->publicado())
            ->when($request->filled('de'), fn ($q) => $q->where('numero', '>=', $request->integer('de')))
            ->when($request->filled('ate'), fn ($q) => $q->where('numero', '<=', $request->integer('ate')))
            ->orderBy('numero')
            ->get()
            ->each(fn (Lote $l) => $l->setRelation('edicao', $edicao));

        return view('organizacao.etiquetas', compact('edicao', 'lotes'));
    }

    public function telao(Request $request): View
    {
        $edicao = $this->edicao($request);
        $atual = $edicao->loteAtual;

        return view('organizacao.telao', [
            'edicao' => $edicao,
            'atual' => $atual,
            'anterior' => $atual ? $this->vizinho($edicao, $atual, -1) : null,
            'proximo' => $atual ? $this->vizinho($edicao, $atual, 1) : $edicao->lotes()->publicado()->orderBy('numero')->first(),
            'arrematados' => $edicao->lotes()->where('status', StatusLote::Arrematado)->count(),
            'totalLotes' => $edicao->lotes()->publicado()->count(),
            'arrecadado' => $edicao->totalArrecadado(),
        ]);
    }

    /** Troca o lote mostrado no telão: por id, por número, anterior/próximo ou tela de espera. */
    public function mostrar(Request $request): RedirectResponse
    {
        $edicao = $this->edicao($request);
        $lote = null;

        if ($request->filled('numero')) {
            $lote = $edicao->lotes()->where('numero', $request->integer('numero'))->first();

            if (! $lote) {
                return back()->with('aviso', "Não existe lote {$request->integer('numero')} nesta edição.");
            }
        } elseif ($request->filled('lote_id')) {
            $lote = $edicao->lotes()->findOrFail($request->integer('lote_id'));
        } elseif ($direcao = $request->input('direcao')) {
            $atual = $edicao->loteAtual;
            $lote = $atual
                ? $this->vizinho($edicao, $atual, $direcao === 'anterior' ? -1 : 1)
                : $edicao->lotes()->publicado()->orderBy('numero')->first();

            if (! $lote) {
                return back()->with('aviso', $direcao === 'anterior' ? 'Este já é o primeiro lote.' : 'Este já é o último lote.');
            }
        }

        $edicao->update(['lote_atual_id' => $lote?->getKey()]);

        return back();
    }

    public function arrematar(Request $request): RedirectResponse
    {
        $edicao = $this->edicao($request);
        $dados = $request->validate(['valor' => ['required', 'numeric', 'min:0']], [], ['valor' => 'valor do lance']);

        $lote = $edicao->loteAtual;
        abort_unless($lote, 422, 'Nenhum lote no telão.');

        $lote->update(['status' => StatusLote::Arrematado, 'valor_arrematado' => $dados['valor']]);

        return back()->with('ok', "Lote {$lote->numero} arrematado por ".Lote::reais($dados['valor']).'.');
    }

    public function desfazerArremate(Request $request): RedirectResponse
    {
        $lote = $this->edicao($request)->loteAtual;
        abort_unless($lote, 422);

        $lote->update(['status' => StatusLote::Disponivel]);

        return back()->with('ok', "Arremate do lote {$lote->numero} desfeito.");
    }

    private function vizinho(Edicao $edicao, Lote $lote, int $sentido): ?Lote
    {
        return $edicao->lotes()->publicado()
            ->where('numero', $sentido > 0 ? '>' : '<', $lote->numero)
            ->orderBy('numero', $sentido > 0 ? 'asc' : 'desc')
            ->first();
    }

    private function edicao(Request $request): Edicao
    {
        $edicao = $request->filled('edicao')
            ? Edicao::where('slug', $request->input('edicao'))->first()
            : Edicao::atual();

        abort_unless($edicao, 404, 'Cadastre uma edição no painel primeiro.');

        return $edicao;
    }
}
