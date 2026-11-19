<!DOCTYPE html>
<html lang="pt-BR" class="bg-marinho-2">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Telão · {{ $edicao->titulo }}</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-dvh overflow-hidden bg-marinho-2 text-white"
    x-data="telao({ url: @js(route('telao.estado', $edicao)), inicial: @js($estado) })"
    @mousemove="mostrarCursor()" :class="cursorOculto && 'cursor-none'">

    <div class="flex h-full flex-col px-[3.5vw] py-[3vh]">
        {{-- Topo: evento e placar --}}
        <header class="flex items-center justify-between gap-6">
            <div class="flex items-center gap-[1vw]">
                <span class="flex size-[4.2vw] min-w-10 items-center justify-center rounded-full bg-cartao"><x-marca class="size-[3vw] min-w-7" /></span>
                <div class="leading-none">
                    <p class="font-display text-[2.4vw] font-black uppercase">{{ $edicao->titulo }}</p>
                    <p class="mt-[0.4vw] text-[1.05vw] font-semibold uppercase tracking-[0.08em] text-white/60">Toda a renda para o Hospital de Amor Amazônia</p>
                </div>
            </div>
            <div class="text-right leading-none">
                <p class="font-display text-[3.4vw] font-black text-sol numeros" x-text="arrecadadoAnimado">{{ $estado['arrecadadoTexto'] }}</p>
                <p class="mt-[0.4vw] text-[1.05vw] font-semibold uppercase tracking-[0.08em] text-white/60">
                    arrecadados · <span x-text="estado.arrematados">{{ $estado['arrematados'] }}</span> de <span x-text="estado.totalLotes">{{ $estado['totalLotes'] }}</span> lotes
                </p>
            </div>
        </header>

        {{-- Lote da vez --}}
        <main class="relative mt-[3vh] min-h-0 flex-1">
            <template x-for="l in (estado.lote ? [estado.lote] : [])" :key="l.id + (l.arrematado ? '-a' : '')">
                <section class="telao-entra grid h-full grid-cols-[minmax(0,1fr)_minmax(0,1.05fr)] items-center gap-[4vw]">
                    <div class="relative flex h-full min-h-0 items-center justify-center">
                        <img :src="l.imagem" :alt="l.nome" x-show="l.imagem"
                            class="max-h-full max-w-full rounded-[1.6vw] object-contain shadow-[0_3vw_6vw_-2vw_rgb(0_0_0/0.55)]"
                            :class="l.arrematado && 'grayscale-[40%]'">
                        <div x-show="l.arrematado" class="telao-carimbo absolute inset-x-[6%] top-1/2 -translate-y-1/2 -rotate-6 rounded-[1vw] border-[0.5vw] border-mata bg-cartao/95 py-[1.4vw] text-center text-mata">
                            <p class="font-display text-[4.6vw] font-black uppercase leading-none">Arrematado</p>
                            <p class="mt-[0.6vw] font-display text-[3vw] font-black leading-none numeros" x-text="l.valorArrematado"></p>
                        </div>
                    </div>
                    <div class="min-w-0">
                        <p class="font-display text-[3vw] font-bold uppercase leading-none text-rosa-claro">Lote</p>
                        <p class="font-display text-[13vw] font-black leading-[0.8] text-sol numeros" x-text="l.numero"></p>
                        <h1 class="mt-[2.4vh] text-[3.6vw] font-bold leading-[1.08]" x-text="l.nome"></h1>
                        <p class="mt-[2vh] text-[1.7vw] text-white/70" x-show="l.doador">Doação de <span class="font-semibold text-white" x-text="l.doador"></span></p>
                        <p class="mt-[3vh] inline-flex items-baseline gap-[0.8vw] rounded-full bg-white/10 px-[1.6vw] py-[0.8vw] text-[1.7vw]" x-show="l.valor && !l.arrematado">
                            <span class="text-white/70" x-text="l.imensuravel ? 'Valor' : 'Valor de referência'"></span>
                            <span class="font-bold numeros" x-text="l.imensuravel ? 'Imensurável' : l.valor"></span>
                        </p>
                    </div>
                </section>
            </template>

            {{-- Tela de espera --}}
            <section x-show="!estado.lote" class="telao-entra grid h-full grid-cols-[1.3fr_1fr] items-center gap-[5vw]" @if ($estado['lote']) x-cloak @endif>
                <div>
                    <h1 class="font-display text-[6.8vw] font-black uppercase leading-[0.98]">
                        Cada lance vira <span class="text-sol">tratamento</span> para alguém da nossa região
                    </h1>
                    <p class="mt-[4vh] max-w-[38vw] text-[1.8vw] leading-snug text-white/75">
                        Veja todos os lotes no celular, marque os seus favoritos e prepare o lance.
                    </p>
                </div>
                <div class="flex flex-col items-center">
                    <div class="w-[27vw] rounded-[1.6vw] bg-cartao p-[1.4vw] [&_svg]:h-auto [&_svg]:w-full">{!! $qrLoja !!}</div>
                    <p class="mt-[2vh] text-[1.6vw] font-semibold">Aponte a câmera e veja a loja</p>
                    <p class="text-[1.3vw] text-white/60">{{ preg_replace('#^https?://#', '', url('/')) }}</p>
                </div>
            </section>
        </main>
    </div>

    <button type="button" x-show="!telaCheia" @click="entrarTelaCheia()"
        class="fixed bottom-4 right-4 rounded-full bg-white/10 px-4 py-2 text-sm font-semibold text-white/80 hover:bg-white/20">
        Tela cheia (F)
    </button>
</body>
</html>
