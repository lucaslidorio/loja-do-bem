@php
    $data = $edicao->data_evento;
    $urlLoja = route('inicio');
    $textoLoja = "Olha os lotes do {$edicao->titulo}! Dia {$data->format('d/m')} em {$edicao->cidade}, toda a renda vai para o Hospital de Amor.";
    $sobre = $edicao->sobre ?: 'O Hospital de Amor, de Barretos (SP), trata pacientes com câncer de todo o Brasil pelo SUS, sem cobrar nada. Boa parte do que ele faz depende de doações, e leilões como este, feitos por voluntários, são uma das maiores fontes. Cada lance aqui vira exame, cirurgia e tratamento para alguém da nossa região.';
@endphp
<x-layout :edicao="$edicao">

    {{-- Abertura: o convite --}}
    <section id="o-leilao" class="mx-auto max-w-6xl px-4 pt-10 sm:px-6 sm:pt-16">
        <div class="grid gap-10 lg:grid-cols-[1.25fr_1fr] lg:items-end lg:gap-14">
            <div>
                <h1 class="font-display text-[3.4rem] font-black uppercase text-marinho sm:text-[5.2rem] lg:text-[5.8rem]">
                    Tudo aqui vai a leilão pelo <span class="text-rosa">Hospital de&nbsp;Amor</span>
                </h1>
                <p class="mt-6 max-w-[34rem] text-lg leading-relaxed text-tinta-2">
                    São {{ $lotes->count() }} lotes doados por {{ $totalDoadores > 1 ? "{$totalDoadores} parceiros" : 'parceiros' }} de {{ $edicao->cidade }} e região.
                    Escolha os seus, marque o <x-icone nome="heart" cheio class="inline size-[1.1em] -translate-y-px text-rosa" /> e venha dar o lance
                    {{ $data->translatedFormat('l') === 'domingo' ? 'no domingo' : 'no dia' }}, {{ $data->translatedFormat('d \\d\\e F') }}.
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="#lotes" class="inline-flex h-12 items-center gap-2 rounded-full bg-rosa px-6 font-semibold text-white transition-colors hover:bg-rosa-2">
                        Ver os lotes <x-icone nome="arrow-right" class="size-4" />
                    </a>
                    <button type="button"
                        x-data="compartilhar({ titulo: @js('Loja do Bem · '.$edicao->titulo), texto: @js($textoLoja), url: @js($urlLoja), whatsapp: @js('https://wa.me/?text='.rawurlencode($textoLoja."\n".$urlLoja)) })"
                        @click="abrir()"
                        class="inline-flex h-12 items-center gap-2 rounded-full border-2 border-tinta/15 bg-cartao px-6 font-semibold transition-colors hover:border-marinho hover:text-marinho">
                        <x-icone nome="arrow-up-on-square" class="size-[1.15rem]" /> Mandar para alguém
                    </button>
                </div>
            </div>

            {{-- Convite --}}
            <aside class="relative overflow-hidden rounded-2xl bg-marinho text-white shadow-[0_18px_40px_-18px_rgb(24_35_92/0.55)]">
                <div class="flex items-stretch">
                    <div class="flex w-[36%] flex-col items-center justify-center border-r-2 border-dashed border-white/25 px-3 py-7 text-center sm:w-[42%] sm:px-4">
                        <span class="text-sm font-semibold uppercase tracking-[0.12em] text-white/75">{{ $data->translatedFormat('l') }}</span>
                        <span class="font-display text-[5.5rem] font-black leading-[0.85] numeros">{{ $data->format('d') }}</span>
                        <span class="font-display text-3xl font-bold uppercase text-sol">{{ $data->translatedFormat('M') }}</span>
                    </div>
                    <div class="flex flex-1 flex-col justify-center gap-3 px-5 py-7 text-[0.95rem]">
                        <p class="flex items-start gap-2.5">
                            <x-icone nome="calendar-days" class="mt-0.5 size-5 text-sol" />
                            <span>A partir das {{ $data->format('H\hi') }}</span>
                        </p>
                        <p class="flex items-start gap-2.5">
                            <x-icone nome="map-pin" class="mt-0.5 size-5 text-sol" />
                            <span>
                                {{ $edicao->local_nome ?? $edicao->cidade }}
                                @if ($edicao->local_endereco)<span class="block text-white/70">{{ $edicao->local_endereco }}</span>@endif
                                <span class="block text-white/70">{{ $edicao->cidade }} – {{ $edicao->uf }}</span>
                            </span>
                        </p>
                        @if ($edicao->convite_info)
                            <p class="flex items-start gap-2.5">
                                <x-icone nome="ticket" class="mt-0.5 size-5 text-sol" />
                                <span>{{ $edicao->convite_info }}</span>
                            </p>
                        @endif
                    </div>
                </div>
                @unless ($edicao->jaAconteceu())
                    <div x-data="contagem({ alvo: @js($data->toIso8601String()) })"
                        class="flex items-center justify-between gap-3 border-t border-white/15 bg-marinho-2 px-5 py-3 text-sm">
                        <span class="text-white/75" x-show="!acabou">Falta pouco</span>
                        <span class="font-semibold numeros" x-show="!acabou">
                            <span x-text="dias"></span> {{ '' }}<span x-text="dias === 1 ? 'dia' : 'dias'">dias</span>,
                            <span x-text="horas"></span>h <span x-text="minutos"></span>min
                        </span>
                        <span class="font-semibold text-sol" x-show="acabou" x-cloak>O leilão está acontecendo agora</span>
                        @if ($edicao->maps_url)
                            <a href="{{ $edicao->maps_url }}" target="_blank" rel="noopener" class="shrink-0 font-semibold text-sol underline">Como chegar</a>
                        @endif
                    </div>
                @endunless
            </aside>
        </div>

        @if ($arrecadado)
            <div class="mt-10 flex flex-wrap items-baseline gap-x-4 gap-y-1 rounded-2xl border-2 border-mata/30 bg-cartao px-6 py-5">
                <span class="font-display text-5xl font-black text-mata numeros">{{ \App\Models\Lote::reais($arrecadado) }}</span>
                <span class="text-tinta-2">arrecadados para o Hospital de Amor nesta edição. Obrigado a cada doador e a cada arrematante.</span>
            </div>
        @endif
    </section>

    {{-- Peças únicas e destaques --}}
    @if ($destaques->isNotEmpty())
        <section class="mt-20" aria-labelledby="titulo-destaques">
            <div class="mx-auto flex max-w-6xl items-end justify-between gap-4 px-4 sm:px-6">
                <h2 id="titulo-destaques" class="font-display text-4xl font-black uppercase text-marinho sm:text-5xl">Não dá para perder</h2>
                <p class="hidden max-w-xs text-right text-sm text-tinta-2 sm:block">Peças autografadas e lotes que vão disputar os maiores lances.</p>
            </div>
            <ul class="mt-6 flex snap-x snap-mandatory scroll-px-4 gap-4 overflow-x-auto px-4 pb-4 sm:scroll-px-6 [scrollbar-width:thin] sm:px-6 lg:mx-auto lg:max-w-6xl lg:grid lg:grid-cols-4 lg:overflow-visible">
                @foreach ($destaques->take(8) as $lote)
                    <x-lote-item :lote="$lote" :interessado="in_array($lote->getKey(), $meusInteresses)" :filtravel="false"
                        class="w-[68%] shrink-0 snap-start sm:w-[40%] lg:w-auto" />
                @endforeach
            </ul>
        </section>
    @endif

    {{-- Catálogo --}}
    <section id="lotes" class="mt-16" x-data="vitrine({ meus: @js($meusInteresses) })" @interesse-alterado.window="marcar($event.detail)" aria-labelledby="titulo-lotes">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <div class="flex items-end justify-between gap-4">
                <h2 id="titulo-lotes" class="font-display text-4xl font-black uppercase text-marinho sm:text-5xl">Todos os lotes</h2>
                <p class="pb-1 text-sm text-tinta-2 numeros" aria-live="polite">
                    <span x-text="visiveis ?? {{ $lotes->count() }}">{{ $lotes->count() }}</span>
                    <span x-text="visiveis === 1 ? 'lote' : 'lotes'">lotes</span>
                </p>
            </div>
        </div>

        {{-- Filtros --}}
        <div class="sticky top-16 z-20 mt-4 border-y border-linha/70 bg-papel/95 backdrop-blur-sm supports-[backdrop-filter]:bg-papel/85">
            <div class="mx-auto flex max-w-6xl flex-col gap-3 px-4 py-3 sm:px-6 md:flex-row md:items-center">
                <label class="relative block md:w-72 md:shrink-0">
                    <span class="sr-only">Buscar lote</span>
                    <x-icone nome="magnifying-glass" class="pointer-events-none absolute left-3.5 top-1/2 size-[1.1rem] -translate-y-1/2 text-tinta-2" />
                    <input type="search" x-model.debounce.150ms="busca" placeholder="Buscar: relógio, chapéu, 120..."
                        class="h-11 w-full rounded-full border border-linha bg-cartao pl-10 pr-4 text-[0.95rem] placeholder:text-tinta-2/80 focus:border-marinho focus:outline-none focus-visible:outline-2 focus-visible:outline-marinho">
                </label>
                <div class="-mx-4 flex gap-2 overflow-x-auto px-4 pb-0.5 [scrollbar-width:none] md:mx-0 md:px-0 md:pr-10 md:[mask-image:linear-gradient(to_right,black_calc(100%-3rem),transparent)]" role="group" aria-label="Filtrar por categoria">
                    @php $chip = 'h-9 shrink-0 rounded-full border px-4 text-sm font-semibold transition-colors'; @endphp
                    <button type="button" @click="categoria = ''; soMeus = false" class="{{ $chip }}"
                        :class="!categoria && !soMeus ? 'border-tinta bg-tinta text-papel' : 'border-linha bg-cartao hover:border-tinta/40'">Todos</button>
                    <button type="button" @click="soMeus = !soMeus; if (soMeus) categoria = ''" class="{{ $chip }} inline-flex items-center gap-1.5"
                        :class="soMeus ? 'border-rosa bg-rosa text-white' : 'border-linha bg-cartao text-rosa hover:border-rosa/50'">
                        <x-icone nome="heart" cheio class="size-4" /> Minha lista
                        <span class="numeros" x-show="meus.size" x-text="'(' + meus.size + ')'"></span>
                    </button>
                    @foreach ($categorias as $cat)
                        <button type="button" @click="soMeus = false; categoria = categoria === @js($cat->slug) ? '' : @js($cat->slug)" class="{{ $chip }}"
                            :class="categoria === @js($cat->slug) ? 'border-marinho bg-marinho text-white' : 'border-linha bg-cartao hover:border-tinta/40'">{{ $cat->nome }}</button>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="mx-auto max-w-6xl px-4 pt-8 sm:px-6">
            @if ($lotes->isEmpty())
                <p class="rounded-2xl border border-dashed border-linha px-6 py-16 text-center text-tinta-2">
                    Os lotes desta edição ainda estão sendo cadastrados. Volte em breve.
                </p>
            @else
                <ul class="grid grid-cols-2 gap-x-4 gap-y-9 sm:gap-x-6 md:grid-cols-3 lg:grid-cols-4">
                    @foreach ($lotes as $lote)
                        <x-lote-item :lote="$lote" :interessado="in_array($lote->getKey(), $meusInteresses)" />
                    @endforeach
                </ul>

                <div x-show="visiveis === 0" x-cloak class="rounded-2xl border border-dashed border-linha px-6 py-14 text-center">
                    <p class="font-semibold" x-show="soMeus && meus.size === 0">Sua lista está vazia.</p>
                    <p class="mt-1 text-tinta-2" x-show="soMeus && meus.size === 0">Toque no coração dos lotes que você quer arrematar. Eles ficam guardados aqui.</p>
                    <p class="font-semibold" x-show="!(soMeus && meus.size === 0)">Nenhum lote encontrado.</p>
                    <button type="button" @click="limpar()" class="mt-4 font-semibold text-rosa underline">Ver todos os lotes</button>
                </div>
            @endif
        </div>
    </section>

    {{-- Para onde vai o dinheiro --}}
    <section class="mx-auto mt-24 max-w-6xl px-4 sm:px-6" aria-labelledby="titulo-causa">
        <div class="grid gap-8 rounded-[20px] bg-rosa px-6 py-10 text-white sm:px-10 sm:py-14 md:grid-cols-[1fr_1.3fr] md:gap-14">
            <h2 id="titulo-causa" class="font-display text-5xl font-black uppercase sm:text-6xl">Para onde vai o seu lance</h2>
            <div>
                <p class="text-lg leading-relaxed text-white/95">{{ $sobre }}</p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="https://wa.me/?text={{ rawurlencode($textoLoja."\n".$urlLoja) }}" target="_blank" rel="noopener"
                        class="inline-flex h-12 items-center gap-2 rounded-full bg-white px-6 font-semibold text-rosa transition-colors hover:bg-rosa-claro">
                        <x-icone nome="arrow-up-on-square" class="size-[1.15rem]" /> Divulgar no WhatsApp
                    </a>
                    <a href="{{ route('doadores', $edicao) }}" class="inline-flex h-12 items-center gap-2 rounded-full border-2 border-white/50 px-6 font-semibold text-white transition-colors hover:border-white">
                        Conheça quem doou
                    </a>
                </div>
            </div>
        </div>
    </section>
</x-layout>
