@php
    use App\Models\Lote;
    $textoCompartilhar = "O {$edicao->titulo} arrecadou ".Lote::reais($total)." para o Hospital de Amor Amazônia. Obrigado a todos que doaram e deram lances!";
    $pctMeta = $edicao->meta_arrecadacao > 0 ? min(100, round($total / $edicao->meta_arrecadacao * 100)) : null;
@endphp
<x-layout :edicao="$edicao" titulo="Resultado" :descricao="$textoCompartilhar">
    @if ($previa)
        <div class="border-b border-sol/40 bg-sol/20">
            <p class="mx-auto max-w-6xl px-4 py-3 text-sm font-semibold sm:px-6">
                Prévia: só quem está logado vê esta página. Para publicar, ligue "Mostrar total arrecadado" na edição, no painel.
            </p>
        </div>
    @endif

    <div class="mx-auto max-w-6xl px-4 pt-12 sm:px-6 sm:pt-20"
        x-data="resultado({ total: @js($total), dados: @js([
            'titulo' => $edicao->titulo,
            'cidade' => "{$edicao->cidade} – {$edicao->uf} · ".$edicao->data_evento->translatedFormat('d \\d\\e F \\d\\e Y'),
            'cidadeCurta' => $edicao->cidade,
            'arrematados' => $qtdArrematados,
            'doadores' => $doadores->count(),
            'slug' => $edicao->slug,
            'marca' => asset('favicon.svg'),
        ]) })">

        <p class="font-display text-3xl font-black uppercase text-marinho sm:text-4xl">Juntos, arrecadamos</p>
        <p x-ref="total" class="mt-5 font-display text-[4.2rem] font-black leading-[0.85] text-rosa numeros sm:text-[8rem] lg:text-[10rem]"
            x-text="totalAnimado">{{ Lote::reais($total) }}</p>
        <p class="mt-6 max-w-[46ch] text-xl leading-relaxed text-tinta-2">
            para o Hospital de Amor Amazônia, em Porto Velho, que trata pacientes com câncer de Rondônia e região pelo SUS, sem cobrar nada.
        </p>

        <div class="mt-8 flex flex-wrap gap-3">
            <button type="button" @click="baixarImagem()" :disabled="gerando"
                class="inline-flex h-12 items-center gap-2 rounded-full bg-rosa px-6 font-semibold text-white transition-colors hover:bg-rosa-2 disabled:opacity-60">
                <x-icone nome="arrow-down-tray" class="size-[1.15rem]" />
                <span x-text="gerando ? 'Gerando...' : 'Baixar imagem para o Instagram'">Baixar imagem para o Instagram</span>
            </button>
            <a href="https://wa.me/?text={{ rawurlencode($textoCompartilhar."\n".route('resultado', $edicao)) }}" target="_blank" rel="noopener"
                class="inline-flex h-12 items-center gap-2 rounded-full border-2 border-tinta/15 bg-cartao px-6 font-semibold transition-colors hover:border-marinho hover:text-marinho">
                <x-icone nome="arrow-up-on-square" class="size-[1.15rem]" /> Mandar no WhatsApp
            </a>
        </div>

        {{-- Números --}}
        <dl class="mt-16 grid gap-px overflow-hidden rounded-2xl bg-linha ring-1 ring-linha sm:grid-cols-3">
            <div class="bg-cartao px-6 py-6">
                <dt class="text-sm text-tinta-2">Lotes arrematados</dt>
                <dd class="mt-1 font-display text-5xl font-black text-marinho numeros">{{ $qtdArrematados }}<span class="text-2xl text-tinta-2"> de {{ $totalLotes }}</span></dd>
            </div>
            <div class="bg-cartao px-6 py-6">
                <dt class="text-sm text-tinta-2">Doadores</dt>
                <dd class="mt-1 font-display text-5xl font-black text-marinho numeros">{{ $doadores->count() }}</dd>
            </div>
            <div class="bg-cartao px-6 py-6">
                @if ($pctMeta !== null)
                    <dt class="text-sm text-tinta-2">Da meta de {{ Lote::reais($edicao->meta_arrecadacao) }}</dt>
                    <dd class="mt-1 font-display text-5xl font-black text-marinho numeros">{{ $pctMeta }}%</dd>
                    <div class="mt-3 h-2 overflow-hidden rounded-full bg-papel-2"><div class="h-full rounded-full bg-mata" style="width: {{ $pctMeta }}%"></div></div>
                @else
                    <dt class="text-sm text-tinta-2">Edição</dt>
                    <dd class="mt-1 font-display text-5xl font-black text-marinho">{{ $edicao->numero }}ª</dd>
                @endif
            </div>
        </dl>

        {{-- Maiores lances --}}
        @if ($maiores->isNotEmpty())
            <section class="mt-20" aria-labelledby="titulo-maiores">
                <h2 id="titulo-maiores" class="font-display text-4xl font-black uppercase text-marinho sm:text-5xl">Os maiores lances</h2>
                <ol class="mt-6 divide-y divide-linha border-y border-linha">
                    @foreach ($maiores as $lote)
                        <li>
                            <a href="{{ $lote->url() }}" class="flex items-center gap-4 py-4 hover:bg-papel-2/50">
                                <span class="w-8 shrink-0 font-display text-3xl font-black text-tinta-2/50 numeros">{{ $loop->iteration }}</span>
                                @if ($img = $lote->imagemUrl('thumb'))
                                    <img src="{{ $img }}" alt="" loading="lazy" class="size-14 shrink-0 rounded-lg object-cover">
                                @endif
                                <span class="min-w-0 flex-1">
                                    <span class="block font-display text-lg font-bold uppercase text-rosa numeros">Lote {{ $lote->numero }}</span>
                                    <span class="block truncate font-semibold">{{ $lote->nome }}</span>
                                </span>
                                <span class="shrink-0 font-display text-2xl font-black text-mata numeros sm:text-3xl">{{ Lote::reais($lote->valor_arrematado) }}</span>
                            </a>
                        </li>
                    @endforeach
                </ol>
            </section>
        @endif

        {{-- Agradecimento --}}
        <section class="mt-20 rounded-[20px] bg-marinho px-6 py-10 text-white sm:px-10 sm:py-14" aria-labelledby="titulo-obrigado">
            <h2 id="titulo-obrigado" class="font-display text-5xl font-black uppercase sm:text-6xl">Obrigado, {{ $edicao->cidade }}!</h2>
            <p class="mt-4 max-w-[60ch] text-lg leading-relaxed text-white/85">
                A cada pessoa que deu um lance, aos voluntários que montaram tudo e a quem doou os lotes deste ano:
            </p>
            @if ($doadores->isNotEmpty())
                <p class="mt-6 text-[0.95rem] leading-loose text-white/90">
                    @foreach ($doadores as $nome)<span class="whitespace-nowrap">{{ $nome }}</span>@unless ($loop->last)<span class="px-2 text-sol" aria-hidden="true">·</span>@endunless @endforeach
                </p>
            @endif
        </section>
    </div>
</x-layout>
