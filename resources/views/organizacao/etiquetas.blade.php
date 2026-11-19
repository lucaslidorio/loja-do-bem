@php use App\Support\QrCode; @endphp
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Etiquetas · {{ $edicao->titulo }}</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    @vite(['resources/css/app.css'])
    <style>
        /* A4 em pé, 8 etiquetas por folha (2 × 4), com margem para cortar */
        @page { size: A4 portrait; margin: 8mm; }
        .folha { width: 194mm; }
        .etiqueta { height: 69.5mm; break-inside: avoid; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .etiqueta .qr svg { width: 100%; height: 100%; display: block; }
        @media print {
            body { background: #fff !important; }
            .so-tela { display: none !important; }
            .folha { margin: 0 !important; box-shadow: none !important; }
        }
    </style>
</head>
<body class="bg-papel-2 text-tinta">
    {{-- Barra de opções: some na impressão --}}
    <div class="so-tela sticky top-0 z-10 border-b border-linha bg-cartao">
        <form method="get" class="mx-auto flex max-w-[194mm] flex-wrap items-end gap-3 px-4 py-3 text-sm">
            <div class="mr-auto">
                <p class="font-display text-2xl font-black uppercase text-marinho">Etiquetas para imprimir</p>
                <p class="text-tinta-2">{{ $lotes->count() }} {{ $lotes->count() === 1 ? 'etiqueta' : 'etiquetas' }} · {{ ceil($lotes->count() / 8) }} {{ ceil($lotes->count() / 8) == 1 ? 'folha' : 'folhas' }} A4</p>
            </div>
            @unless (request()->filled('ids'))
                <label class="flex flex-col gap-1">
                    <span class="font-semibold">Do lote</span>
                    <input type="number" name="de" value="{{ request('de') }}" class="h-10 w-24 rounded-lg border border-linha bg-white px-3">
                </label>
                <label class="flex flex-col gap-1">
                    <span class="font-semibold">Até o lote</span>
                    <input type="number" name="ate" value="{{ request('ate') }}" class="h-10 w-24 rounded-lg border border-linha bg-white px-3">
                </label>
                <button class="h-10 rounded-full border-2 border-tinta/15 px-4 font-semibold hover:border-marinho">Filtrar</button>
            @else
                <a href="{{ route('organizacao.etiquetas') }}" class="h-10 content-center font-semibold text-rosa underline">Ver todos os lotes</a>
            @endunless
            <button type="button" onclick="window.print()" class="h-10 rounded-full bg-rosa px-5 font-semibold text-white hover:bg-rosa-2">Imprimir</button>
        </form>
        @if (str_contains(request()->getHost(), 'localhost') || request()->getHost() === '127.0.0.1')
            <p class="mx-auto max-w-[194mm] px-4 pb-3 text-sm font-semibold text-rosa">
                Atenção: você está no computador local. Os QR Codes apontam para {{ url('/') }} e não vão abrir no celular de ninguém.
                Imprima as etiquetas pelo site publicado.
            </p>
        @endif
    </div>

    <main class="folha mx-auto my-6 grid grid-cols-2 bg-white shadow-[0_10px_30px_-12px_rgb(27_29_54/0.25)]">
        @forelse ($lotes as $lote)
            <article class="etiqueta flex items-stretch gap-[4mm] border border-dashed border-linha p-[5mm]">
                <div class="flex min-w-0 flex-1 flex-col">
                    <div class="flex items-center gap-1.5">
                        <x-marca class="size-[6mm]" />
                        <span class="text-[2.6mm] font-bold uppercase leading-tight tracking-[0.04em] text-marinho">{{ $edicao->titulo }}</span>
                    </div>
                    <p class="mt-[2mm] font-display text-[6mm] font-bold uppercase leading-none text-rosa">Lote</p>
                    <p class="font-display text-[19mm] font-black leading-[0.82] text-marinho numeros">{{ $lote->numero }}</p>
                    <p class="mt-[2mm] line-clamp-3 text-[3.6mm] font-bold leading-snug">{{ $lote->nome }}</p>
                    @if ($lote->doador)
                        <p class="mt-auto truncate pt-[1mm] text-[2.8mm] text-tinta-2">Doação de {{ $lote->doador->nome }}</p>
                    @endif
                </div>
                <div class="flex w-[34mm] shrink-0 flex-col items-center justify-center text-center">
                    <div class="qr size-[32mm]">{!! QrCode::svg($lote->url(), 256) !!}</div>
                    <p class="mt-[1.5mm] text-[2.5mm] font-semibold leading-tight text-tinta-2">Aponte a câmera para ver detalhes e compartilhar</p>
                </div>
            </article>
        @empty
            <p class="col-span-2 px-6 py-16 text-center text-tinta-2">Nenhum lote encontrado com esse filtro.</p>
        @endforelse
    </main>
</body>
</html>
