@props([
    'edicao',
    'titulo' => null,
    'descricao' => null,
    'imagem' => null,
    'canonical' => null,
])
@php
    $tituloPagina = $titulo ? "{$titulo} · Loja do Bem" : "Loja do Bem · {$edicao->titulo}";
    $descricao ??= "Conheça os lotes do {$edicao->titulo}, dia {$edicao->data_evento->translatedFormat('d \\d\\e F')} em {$edicao->cidade}. Toda a renda vai para o Hospital de Amor Amazônia.";
    $canonical ??= url()->current();
    $imagem ??= asset('og-padrao.jpg');
@endphp
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#f6f1ea">

    <title>{{ $tituloPagina }}</title>
    <meta name="description" content="{{ $descricao }}">
    <link rel="canonical" href="{{ $canonical }}">

    {{-- Prévia ao compartilhar no WhatsApp, Facebook e Instagram --}}
    <meta property="og:type" content="website">
    <meta property="og:locale" content="pt_BR">
    <meta property="og:site_name" content="Loja do Bem · {{ $edicao->titulo }}">
    <meta property="og:title" content="{{ $titulo ?? $edicao->titulo }}">
    <meta property="og:description" content="{{ $descricao }}">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:image" content="{{ $imagem }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta name="twitter:card" content="summary_large_image">

    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{ $head ?? '' }}
</head>
<body class="min-h-dvh bg-papel text-tinta">
    <a href="#conteudo" class="sr-only focus:not-sr-only focus:fixed focus:left-3 focus:top-3 focus:z-50 focus:rounded-lg focus:bg-tinta focus:px-4 focus:py-2 focus:text-papel">Pular para o conteúdo</a>

    <header class="sticky top-0 z-30 border-b border-linha/80 bg-papel/95 backdrop-blur-sm supports-[backdrop-filter]:bg-papel/85">
        <div class="mx-auto flex h-16 max-w-6xl items-center gap-4 px-4 sm:px-6">
            <a href="{{ route('inicio') }}" class="group flex items-center gap-2.5" aria-label="Loja do Bem, página inicial">
                <x-marca class="h-9 w-9" />
                <span class="leading-none">
                    <span class="block font-display text-[1.45rem] font-black uppercase text-rosa">Loja do Bem</span>
                    <span class="block whitespace-nowrap text-[0.62rem] font-semibold uppercase tracking-[0.06em] text-marinho sm:text-[0.7rem] sm:tracking-[0.08em]">{{ $edicao->numero }}º Leilão Direito de Viver</span>
                </span>
            </a>
            <nav class="ml-auto flex items-center gap-1 text-sm font-semibold" aria-label="Principal">
                <a href="{{ route('inicio') }}#lotes" class="hidden rounded-full px-3 py-2 hover:bg-papel-2 sm:block">Lotes</a>
                <a href="{{ route('doadores', $edicao) }}" class="rounded-full px-3 py-2 hover:bg-papel-2">Doadores</a>
                <a href="{{ route('inicio') }}#o-leilao" class="rounded-full bg-marinho px-3.5 py-2 text-white hover:bg-marinho-2">
                    {{ $edicao->data_evento->format('d/m') }}
                </a>
            </nav>
        </div>
    </header>

    <main id="conteudo">
        {{ $slot }}
    </main>

    <footer class="mt-24 border-t border-linha bg-papel-2/60">
        <div class="mx-auto grid max-w-6xl gap-10 px-4 py-12 sm:px-6 md:grid-cols-[1.4fr_1fr_1fr]">
            <div>
                <p class="font-display text-3xl font-black uppercase text-marinho">{{ $edicao->titulo }}</p>
                <p class="mt-3 max-w-sm text-sm text-tinta-2">
                    Evento voluntário de {{ $edicao->cidade }} – {{ $edicao->uf }}. Tudo o que é arrecadado vai para o
                    Hospital de Amor Amazônia, em Porto Velho, que trata pacientes com câncer pelo SUS, sem cobrar nada.
                </p>
            </div>
            <div class="text-sm">
                <p class="font-semibold">Quando e onde</p>
                <p class="mt-2 text-tinta-2">{{ ucfirst($edicao->data_evento->translatedFormat('l, d \\d\\e F \\à\\s H\\hi')) }}</p>
                @if ($edicao->local_nome)
                    <p class="text-tinta-2">{{ $edicao->local_nome }}</p>
                @endif
                @if ($edicao->maps_url)
                    <a href="{{ $edicao->maps_url }}" class="mt-2 inline-flex items-center gap-1.5 font-semibold text-rosa underline" target="_blank" rel="noopener">
                        <x-icone nome="map-pin" class="size-4" /> Ver no mapa
                    </a>
                @endif
            </div>
            <div class="text-sm">
                <p class="font-semibold">Fale com a organização</p>
                <ul class="mt-2 space-y-1.5">
                    @if ($wa = $edicao->whatsappUrl('Olá! Vi a Loja do Bem e quero saber mais sobre o leilão.'))
                        <li><a href="{{ $wa }}" class="font-semibold text-rosa underline" target="_blank" rel="noopener">WhatsApp</a></li>
                    @endif
                    @if ($edicao->instagram)
                        <li><a href="https://instagram.com/{{ $edicao->instagram }}" class="font-semibold text-rosa underline" target="_blank" rel="noopener">{{ '@'.$edicao->instagram }}</a></li>
                    @endif
                    <li><a href="{{ route('doadores', $edicao) }}" class="text-tinta-2 underline">Quem doou</a></li>
                </ul>
            </div>
        </div>
    </footer>
</body>
</html>
