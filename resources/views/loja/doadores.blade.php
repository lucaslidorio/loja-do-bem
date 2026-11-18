<x-layout :edicao="$edicao" titulo="Quem doou" :descricao="'Empresas e pessoas que doaram os lotes do '.$edicao->titulo.'. Obrigado!'">
    <div class="mx-auto max-w-6xl px-4 pt-10 sm:px-6 sm:pt-16">
        <h1 class="max-w-3xl font-display text-[3.2rem] font-black uppercase text-marinho sm:text-[4.6rem]">
            Esse leilão só existe porque <span class="text-rosa">alguém doou</span>
        </h1>
        <p class="mt-5 max-w-[60ch] text-lg leading-relaxed text-tinta-2">
            {{ $doadores->count() }} empresas e pessoas de {{ $edicao->cidade }} e região doaram os lotes do {{ $edicao->titulo }}.
            Quando passar por elas, agradeça. E se você tem um negócio e quer doar no próximo ano, fale com a organização.
        </p>

        @if ($doadores->isEmpty())
            <p class="mt-12 rounded-2xl border border-dashed border-linha px-6 py-16 text-center text-tinta-2">A lista de doadores aparece aqui assim que os lotes forem cadastrados.</p>
        @else
            <ul class="mt-12 divide-y divide-linha border-y border-linha">
                @foreach ($doadores as $doador)
                    <li class="grid gap-4 py-6 md:grid-cols-[minmax(0,1fr)_minmax(0,1.6fr)] md:gap-10">
                        <div class="flex items-center gap-4">
                            @if ($logo = $doador->logoUrl())
                                <img src="{{ $logo }}" alt="" loading="lazy" class="size-14 shrink-0 rounded-full bg-cartao object-contain ring-1 ring-linha">
                            @else
                                <span class="flex size-14 shrink-0 items-center justify-center rounded-full bg-rosa-claro font-display text-2xl font-black text-rosa" aria-hidden="true">
                                    {{ mb_strtoupper(mb_substr($doador->nome, 0, 1)) }}
                                </span>
                            @endif
                            <div class="min-w-0">
                                <h2 class="text-lg font-bold leading-snug">{{ $doador->nome }}</h2>
                                <p class="text-sm text-tinta-2">
                                    {{ $doador->lotes->count() }} {{ $doador->lotes->count() === 1 ? 'lote' : 'lotes' }}
                                    @if ($doador->cidade) · {{ $doador->cidade }} @endif
                                    @if ($ig = $doador->instagramUrl())
                                        · <a href="{{ $ig }}" target="_blank" rel="noopener" class="font-semibold text-rosa underline">{{ '@'.$doador->instagram }}</a>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <ul class="flex flex-wrap content-center gap-2">
                            @foreach ($doador->lotes as $lote)
                                <li>
                                    <a href="{{ $lote->setRelation('edicao', $edicao)->url() }}" class="inline-flex items-center gap-2 rounded-full border border-linha bg-cartao py-1.5 pl-1.5 pr-3.5 text-sm hover:border-tinta/40">
                                        <span class="rounded-full bg-papel-2 px-2 py-0.5 font-display font-bold text-rosa numeros">{{ $lote->numero }}</span>
                                        <span class="max-w-[16rem] truncate">{{ $lote->nome }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</x-layout>
