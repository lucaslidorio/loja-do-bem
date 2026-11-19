<?php

use App\Http\Controllers\LojaController;
use App\Http\Controllers\OrganizacaoController;
use App\Http\Controllers\ResultadoController;
use App\Http\Controllers\TelaoController;
use App\Http\Middleware\IdentificaVisitante;
use Illuminate\Support\Facades\Route;

// O painel fica em /painel (Filament). Edições usam o ano no endereço: /2026
Route::pattern('edicao', '[0-9][a-z0-9-]*');

// Uso da organização (login do painel)
Route::middleware('auth')->prefix('organizacao')->name('organizacao.')->group(function () {
    Route::get('/etiquetas', [OrganizacaoController::class, 'etiquetas'])->name('etiquetas');
    Route::get('/telao', [OrganizacaoController::class, 'telao'])->name('telao');
    Route::post('/telao/mostrar', [OrganizacaoController::class, 'mostrar'])->name('telao.mostrar');
    Route::post('/telao/arrematar', [OrganizacaoController::class, 'arrematar'])->name('telao.arrematar');
    Route::post('/telao/desfazer', [OrganizacaoController::class, 'desfazerArremate'])->name('telao.desfazer');
});

// Telão: tela cheia para projetar no evento (só leitura)
Route::get('/{edicao}/telao', [TelaoController::class, 'tela'])->name('telao');
Route::get('/{edicao}/telao/estado', [TelaoController::class, 'estado'])->name('telao.estado');

Route::middleware(IdentificaVisitante::class)->group(function () {
    Route::get('/', [LojaController::class, 'inicio'])->name('inicio');
    Route::get('/{edicao}', [LojaController::class, 'edicao'])->name('edicao');
    Route::get('/{edicao}/lote/{referencia}', [LojaController::class, 'lote'])->name('lote');
    Route::get('/{edicao}/doadores', [LojaController::class, 'doadores'])->name('doadores');
    Route::get('/{edicao}/resultado', [ResultadoController::class, 'show'])->name('resultado');

    Route::post('/interesse/{lote}', [LojaController::class, 'interesse'])
        ->middleware('throttle:40,1')
        ->name('interesse');
});
