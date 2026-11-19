@php use App\Models\Lote; @endphp
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="robots" content="noindex">
    <meta name="theme-color" content="#18235c">
    <title>Controle do telão · {{ $edicao->titulo }}</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-dvh bg-papel pb-10 text-tinta">
    @php
        $botao = 'inline-flex h-14 items-center justify-center gap-2 rounded-2xl font-semibold transition-colors disabled:opacity-40';
        $form = fn (array $campos) => collect($campos)->map(fn ($v, $k) => '<input type="hidden" name="'.e($k).'" value="'.e($v).'">')->implode('');
    @endphp

    <header class="sticky top-0 z-10 bg-marinho-2 text-white">
        <div class="mx-auto flex max-w-md items-center justify-between gap-3 px-4 py-3">
            <div class="leading-tight">
                <p class="font-display text-xl font-black uppercase">Controle do telão</p>
                <p class="text-xs text-white/70">{{ $edicao->titulo }}</p>
            </div>
            <div class="text-right leading-tight">
                <p class="font-display text-xl font-black text-sol numeros">{{ Lote::reais($arrecadado) }}</p>
                <p class="text-xs text-white/70 numeros">{{ $arrematados }} de {{ $totalLotes }} arrematados</p>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-md px-4 pt-4">
        @if (session('ok'))
            <p class="mb-3 rounded-xl bg-mata/15 px-4 py-3 text-sm font-semibold text-mata" role="status">{{ session('ok') }}</p>
        @endif
        @if (session('aviso') || $errors->any())
            <p class="mb-3 rounded-xl bg-rosa-claro px-4 py-3 text-sm font-semibold text-rosa-2" role="alert">{{ session('aviso') ?? $errors->first() }}</p>
        @endif

        {{-- O que está no telão agora --}}
        <section class="overflow-hidden rounded-2xl bg-cartao ring-1 ring-linha">
            <p class="flex items-center gap-2 border-b border-linha px-4 py-2 text-xs font-bold uppercase tracking-[0.08em] text-tinta-2">
                <span class="size-2 rounded-full bg-rosa"></span> No telão agora
            </p>
            @if ($atual)
                <div class="flex gap-4 p-4">
                    @if ($img = $atual->imagemUrl('thumb'))
                        <img src="{{ $img }}" alt="" class="size-24 shrink-0 rounded-xl object-cover">
                    @endif
                    <div class="min-w-0">
                        <p class="font-display text-4xl font-black leading-none text-rosa numeros">{{ $atual->numero }}</p>
                        <p class="mt-1 font-semibold leading-snug">{{ $atual->nome }}</p>
                        <p class="mt-1 text-sm text-tinta-2">
                            @if ($atual->arrematado())
                                <span class="font-semibold text-mata">Arrematado por {{ Lote::reais($atual->valor_arrematado) }}</span>
                            @else
                                {{ $atual->valorTexto() ? 'Ref. '.$atual->valorTexto() : 'Sem valor de referência' }}
                            @endif
                        </p>
                    </div>
                </div>
            @else
                <p class="px-4 py-6 text-tinta-2">Tela de espera (QR Code da loja). Toque em <strong>Próximo</strong> para começar.</p>
            @endif
        </section>

        {{-- Navegação --}}
        <div class="mt-3 grid grid-cols-2 gap-3">
            <form method="post" action="{{ route('organizacao.telao.mostrar') }}">
                @csrf {!! $form(['direcao' => 'anterior']) !!}
                <button class="{{ $botao }} w-full border-2 border-tinta/15 bg-cartao" @disabled(! $anterior)>
                    <x-icone nome="arrow-left" class="size-5" />
                    {{ $anterior ? 'Lote '.$anterior->numero : 'Anterior' }}
                </button>
            </form>
            <form method="post" action="{{ route('organizacao.telao.mostrar') }}">
                @csrf {!! $form(['direcao' => 'proximo']) !!}
                <button class="{{ $botao }} w-full bg-marinho text-white" @disabled(! $proximo)>
                    {{ $proximo ? 'Lote '.$proximo->numero : 'Próximo' }}
                    <x-icone nome="arrow-right" class="size-5" />
                </button>
            </form>
        </div>

        {{-- Arremate --}}
        @if ($atual)
            @if ($atual->arrematado())
                <form method="post" action="{{ route('organizacao.telao.desfazer') }}" class="mt-3" onsubmit="return confirm('Desfazer o arremate do lote {{ $atual->numero }}?')">
                    @csrf
                    <button class="{{ $botao }} w-full border-2 border-tinta/15 bg-cartao text-tinta-2">Desfazer arremate</button>
                </form>
            @else
                <form method="post" action="{{ route('organizacao.telao.arrematar') }}" class="mt-3 rounded-2xl bg-cartao p-4 ring-1 ring-linha">
                    @csrf
                    <label for="valor" class="text-sm font-semibold">Lance vencedor do lote {{ $atual->numero }}</label>
                    <div class="mt-2 flex gap-2">
                        <div class="relative flex-1">
                            <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 font-semibold text-tinta-2">R$</span>
                            <input id="valor" name="valor" type="number" inputmode="decimal" step="0.01" min="0" required
                                class="h-14 w-full rounded-2xl border border-linha bg-white pl-12 pr-3 text-xl font-bold numeros focus:border-mata focus:outline-none">
                        </div>
                        <button class="{{ $botao }} bg-mata px-5 text-white">
                            <x-icone nome="check" class="size-5" /> Arrematado
                        </button>
                    </div>
                    <p class="mt-2 text-xs text-tinta-2">O telão mostra o carimbo e o total sobe na hora.</p>
                </form>
            @endif
        @endif

        {{-- Ir para um lote --}}
        <form method="post" action="{{ route('organizacao.telao.mostrar') }}" class="mt-3 flex gap-2">
            @csrf
            <input name="numero" type="number" inputmode="numeric" placeholder="Ir para o lote nº" required
                class="h-14 min-w-0 flex-1 rounded-2xl border border-linha bg-cartao px-4 text-lg font-semibold focus:border-marinho focus:outline-none">
            <button class="{{ $botao }} border-2 border-tinta/15 bg-cartao px-5">Mostrar</button>
        </form>

        <div class="mt-6 grid grid-cols-2 gap-3 text-sm">
            <form method="post" action="{{ route('organizacao.telao.mostrar') }}">
                @csrf
                <button class="{{ $botao }} h-12 w-full border border-linha bg-transparent text-tinta-2" @disabled(! $atual)>Tela de espera</button>
            </form>
            <a href="{{ route('telao', $edicao) }}" target="_blank" class="{{ $botao }} h-12 border border-linha text-tinta-2">Abrir o telão</a>
        </div>
        <p class="mt-6 text-center text-xs text-tinta-2">
            Abra o telão no computador ligado ao projetor e este controle no seu celular.
            <a href="{{ url('/painel') }}" class="underline">Voltar ao painel</a>
        </p>
    </main>
</body>
</html>
