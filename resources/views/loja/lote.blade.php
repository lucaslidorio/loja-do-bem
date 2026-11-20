@php
    $data = $edicao->data_evento;
    $texto = $lote->textoCompartilhar(false);
    $descricaoMeta = "Lote {$lote->numero} do {$edicao->titulo}"
        .($lote->doador ? ", doado por {$lote->doador->nome}" : '')
        .". Vai a leilão em {$data->format('d/m')}, em {$edicao->cidade}. Renda para o Hospital de Amor Amazônia.";
@endphp
<x-layout :edicao="$edicao" :titulo="'Lote '.$lote->numero.' · '.$lote->nome" :descricao="$descricaoMeta" :imagem="$lote->ogImagemUrl()" :canonical="$lote->url()">
    <x-slot:head>
        <script type="application/ld+json">{!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $lote->nome,
            'description' => $lote->descricao ?: $descricaoMeta,
            'image' => $lote->imagemUrl(),
            'sku' => 'lote-'.$lote->numero,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    </x-slot:head>

    <div class="mx-auto max-w-6xl px-4 pt-6 sm:px-6 sm:pt-10">
        <a href="{{ route('inicio') }}#lotes" class="inline-flex items-center gap-1.5 text-sm font-semibold text-tinta-2 hover:text-tinta">
            <x-icone nome="arrow-left" class="size-4" /> Todos os lotes
        </a>

        <article class="mt-5 grid gap-8 md:grid-cols-[1.1fr_1fr] md:gap-12 lg:gap-16">
            {{-- Imagem --}}
            <div class="md:sticky md:top-24 md:self-start">
                <div class="relative overflow-hidden rounded-[18px] bg-papel-2">
                    @if ($src = $lote->imagemUrl())
                        <img src="{{ $src }}" alt="{{ $lote->nome }}" width="1600" height="2000" fetchpriority="high"
                            @class(['w-full object-cover', 'aspect-[4/5]' => $lote->imagem_path, 'aspect-square' => ! $lote->imagem_path, 'grayscale-[50%]' => $lote->arrematado()])>
                    @else
                        <div class="flex aspect-[4/5] items-end bg-[linear-gradient(160deg,var(--color-papel-2),var(--color-linha))] p-8">
                            <span class="font-display text-[9rem] font-black text-cartao/90">{{ $lote->numero }}</span>
                        </div>
                    @endif
                    @if ($lote->arrematado())
                        <span class="absolute inset-x-8 top-1/2 -translate-y-1/2 -rotate-6 rounded-lg border-4 border-mata bg-cartao/90 py-3 text-center font-display text-4xl font-black uppercase text-mata">
                            Arrematado
                        </span>
                    @endif
                </div>
                @if ($lote->arte_path && $lote->imagem_path)
                    <a href="{{ $lote->arteUrl() }}" download class="mt-3 inline-flex items-center gap-1.5 text-sm font-semibold text-tinta-2 underline hover:text-tinta">
                        <x-icone nome="arrow-down-tray" class="size-4" /> Baixar a arte para postar
                    </a>
                @endif
            </div>

            {{-- Detalhes --}}
            <div class="md:pt-2">
                <p class="font-display text-[4.5rem] font-black uppercase leading-[0.85] text-rosa numeros sm:text-[6rem]">
                    <span class="sr-only">Lote </span>{{ $lote->numero }}
                </p>
                <h1 class="mt-3 text-[1.9rem] font-bold leading-tight text-marinho sm:text-[2.3rem]">{{ $lote->nome }}</h1>

                <dl class="mt-6 divide-y divide-linha border-y border-linha text-[0.95rem]">
                    @if ($valor = $lote->valorTexto())
                        <div class="flex items-baseline justify-between gap-4 py-3">
                            <dt class="text-tinta-2">{{ $lote->valor_imensuravel ? 'Valor' : 'Valor de referência' }}</dt>
                            <dd class="font-semibold numeros {{ $lote->valor_imensuravel ? 'text-marinho' : '' }}">{{ $lote->valor_imensuravel ? 'Imensurável' : $valor }}</dd>
                        </div>
                    @endif
                    @if ($lote->arrematado())
                        <div class="flex items-baseline justify-between gap-4 py-3">
                            <dt class="text-tinta-2">Arrematado por</dt>
                            <dd class="font-semibold text-mata numeros">{{ \App\Models\Lote::reais($lote->valor_arrematado) }}</dd>
                        </div>
                    @endif
                    @if ($lote->doador)
                        <div class="flex items-baseline justify-between gap-4 py-3">
                            <dt class="text-tinta-2">Doação de</dt>
                            <dd class="text-right font-semibold">
                                @if ($ig = $lote->doador->instagramUrl())
                                    <a href="{{ $ig }}" target="_blank" rel="noopener" class="underline decoration-linha hover:decoration-rosa">{{ $lote->doador->nome }}</a>
                                @else
                                    {{ $lote->doador->nome }}
                                @endif
                            </dd>
                        </div>
                    @endif
                    @if ($lote->categoria)
                        <div class="flex items-baseline justify-between gap-4 py-3">
                            <dt class="text-tinta-2">Categoria</dt>
                            <dd><a href="{{ route('inicio', ['categoria' => $lote->categoria->slug]) }}#lotes" class="font-semibold underline decoration-linha hover:decoration-rosa">{{ $lote->categoria->nome }}</a></dd>
                        </div>
                    @endif
                </dl>

                @if ($lote->descricao)
                    <div class="mt-6 max-w-[62ch] whitespace-pre-line leading-relaxed text-tinta">{{ $lote->descricao }}</div>
                @endif

                @unless ($lote->arrematado())
                    <div class="mt-8 flex flex-wrap gap-3">
                        <x-botao-interesse :lote="$lote" :ativo="$interessado" grande />
                        <div x-data="compartilhar({ titulo: @js('Lote '.$lote->numero.' · '.$lote->nome), texto: @js($texto), url: @js($lote->url()), whatsapp: @js('https://wa.me/?text='.rawurlencode($texto."\n".$lote->url())) })" class="flex flex-wrap gap-3">
                            <button type="button" @click="abrir()"
                                class="inline-flex h-12 items-center gap-2 rounded-full bg-marinho px-5 font-semibold text-white transition-colors hover:bg-marinho-2">
                                <x-icone nome="share" class="size-[1.15rem]" /> Compartilhar
                            </button>
                            <button type="button" @click="copiar()" title="Copia o endereço deste lote para você colar onde quiser"
                                class="inline-flex h-12 items-center gap-2 rounded-full border-2 border-tinta/15 bg-cartao px-5 font-semibold transition-colors hover:border-marinho hover:text-marinho"
                                :class="copiado && 'border-mata/50 text-mata hover:border-mata/50 hover:text-mata'">
                                <x-icone nome="link" class="size-5" x-show="!copiado" />
                                <x-icone nome="check" class="size-5" x-show="copiado" x-cloak />
                                <span x-text="copiado ? 'Link copiado' : 'Copiar link'">Copiar link</span>
                            </button>
                        </div>
                    </div>
                    <p class="mt-3 text-sm text-tinta-2">O coração só guarda o lote na sua lista. Não é um lance.</p>

                    {{-- Como arrematar --}}
                    <div class="mt-10 rounded-2xl bg-cartao p-6 ring-1 ring-linha">
                        <h2 class="font-display text-3xl font-black uppercase text-marinho">Como arrematar</h2>
                        <p class="mt-3 leading-relaxed text-tinta-2">
                            Os lances são só presenciais, no dia do leilão:
                            <strong class="font-semibold text-tinta">{{ $data->translatedFormat('l, d \\d\\e F') }}, a partir das {{ $data->format('H\hi') }}</strong>,
                            no {{ $edicao->local_nome ?? 'local do evento' }}, em {{ $edicao->cidade }}.
                            @if ($edicao->convite_info) {{ $edicao->convite_info }} @endif
                        </p>
                        @if ($edicao->maps_url || $edicao->whatsapp)
                        <div class="mt-5 flex flex-wrap gap-x-6 gap-y-2 text-sm font-semibold">
                            @if ($edicao->maps_url)
                                <a href="{{ $edicao->maps_url }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 text-rosa underline"><x-icone nome="map-pin" class="size-4" /> Como chegar</a>
                            @endif
                            @if ($wa = $edicao->whatsappUrl("Olá! Tenho interesse no lote {$lote->numero} ({$lote->nome}). Como faço?"))
                                <a href="{{ $wa }}" target="_blank" rel="noopener" class="text-rosa underline">Tirar dúvida pelo WhatsApp</a>
                            @endif
                        </div>
                        @endif
                    </div>
                @endunless

                {{-- Anterior / próximo --}}
                <nav class="mt-10 grid grid-cols-2 gap-3 text-sm" aria-label="Outros lotes">
                    @if ($anterior)
                        <a href="{{ $anterior->url() }}" rel="prev" class="group rounded-xl border border-linha px-4 py-3 hover:border-tinta/40">
                            <span class="flex items-center gap-1 text-tinta-2"><x-icone nome="arrow-left" class="size-3.5" /> Lote {{ $anterior->numero }}</span>
                            <span class="mt-0.5 block truncate font-semibold">{{ $anterior->nome }}</span>
                        </a>
                    @else <span></span> @endif
                    @if ($proximo)
                        <a href="{{ $proximo->url() }}" rel="next" class="group rounded-xl border border-linha px-4 py-3 text-right hover:border-tinta/40">
                            <span class="flex items-center justify-end gap-1 text-tinta-2">Lote {{ $proximo->numero }} <x-icone nome="arrow-right" class="size-3.5" /></span>
                            <span class="mt-0.5 block truncate font-semibold">{{ $proximo->nome }}</span>
                        </a>
                    @endif
                </nav>
            </div>
        </article>

        @if ($vizinhos->isNotEmpty())
            <section class="mt-20" aria-labelledby="titulo-vizinhos">
                <h2 id="titulo-vizinhos" class="font-display text-4xl font-black uppercase text-marinho">Veja também</h2>
                <ul class="mt-6 grid grid-cols-2 gap-x-4 gap-y-9 sm:gap-x-6 md:grid-cols-4">
                    @foreach ($vizinhos as $v)
                        <x-lote-item :lote="$v->setRelation('edicao', $edicao)" :filtravel="false" />
                    @endforeach
                </ul>
            </section>
        @endif
    </div>
</x-layout>
