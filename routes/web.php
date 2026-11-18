<?php

use App\Http\Controllers\LojaController;
use App\Http\Middleware\IdentificaVisitante;
use Illuminate\Support\Facades\Route;

// O painel fica em /painel (Filament). Edições usam o ano no endereço: /2026
Route::pattern('edicao', '[0-9][a-z0-9-]*');

Route::middleware(IdentificaVisitante::class)->group(function () {
    Route::get('/', [LojaController::class, 'inicio'])->name('inicio');
    Route::get('/{edicao}', [LojaController::class, 'edicao'])->name('edicao');
    Route::get('/{edicao}/lote/{referencia}', [LojaController::class, 'lote'])->name('lote');
    Route::get('/{edicao}/doadores', [LojaController::class, 'doadores'])->name('doadores');

    Route::post('/interesse/{lote}', [LojaController::class, 'interesse'])
        ->middleware('throttle:40,1')
        ->name('interesse');
});
