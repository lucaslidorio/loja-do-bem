<?php

namespace App\Http\Controllers;

use App\Enums\StatusLote;
use App\Models\Edicao;
use App\Models\Lote;
use App\Support\QrCode;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

/**
 * Telão para projetar durante o leilão. A tela consulta o estado a cada
 * 2 segundos (a hospedagem compartilhada não tem WebSocket).
 */
class TelaoController extends Controller
{
    public function tela(Edicao $edicao): View
    {
        return view('loja.telao', [
            'edicao' => $edicao,
            'estado' => $this->dados($edicao),
            'qrLoja' => QrCode::svg(route('inicio'), 320, '#1b1d36', '#fffdf9'),
        ]);
    }

    public function estado(Edicao $edicao): JsonResponse
    {
        return response()->json($this->dados($edicao))->header('Cache-Control', 'no-store');
    }

    private function dados(Edicao $edicao): array
    {
        $lote = $edicao->loteAtual()->with('doador')->first();

        return [
            'lote' => $lote ? [
                'id' => $lote->getKey(),
                'numero' => $lote->numero,
                'nome' => $lote->nome,
                'doador' => $lote->doador?->nome,
                'valor' => $lote->valorTexto(),
                'imensuravel' => $lote->valor_imensuravel,
                'imagem' => $lote->imagemUrl('full'),
                'arrematado' => $lote->arrematado(),
                'valorArrematado' => $lote->arrematado() ? Lote::reais($lote->valor_arrematado) : null,
            ] : null,
            'arrecadado' => $edicao->totalArrecadado(),
            'arrecadadoTexto' => Lote::reais($edicao->totalArrecadado()),
            'arrematados' => $edicao->lotes()->where('status', StatusLote::Arrematado)->count(),
            'totalLotes' => $edicao->lotes()->publicado()->count(),
        ];
    }
}
