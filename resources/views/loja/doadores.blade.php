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
            <ul class="mt-12 grid gap-x-8 border-t border-linha sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($doadores as $doador)
                    <li class="flex items-center gap-4 border-b border-linha py-5">
                        @if ($logo = $doador->logoUrl())
                            <img src="{{ $logo }}" alt="" loading="lazy" class="size-12 shrink-0 rounded-full bg-cartao object-contain ring-1 ring-linha">
                        @else
                            <span class="flex size-12 shrink-0 items-center justify-center rounded-full bg-rosa-claro font-display text-2xl font-black text-rosa" aria-hidden="true">
                                {{ mb_strtoupper(mb_substr($doador->nome, 0, 1)) }}
                            </span>
                        @endif
                        <div class="min-w-0">
                            <h2 class="font-bold leading-snug">{{ $doador->nome }}</h2>
                            @if ($doador->cidade || $doador->instagram)
                                <p class="text-sm text-tinta-2">
                                    {{ $doador->cidade }}
                                    @if ($doador->cidade && $doador->instagram) · @endif
                                    @if ($ig = $doador->instagramUrl())
                                        <a href="{{ $ig }}" target="_blank" rel="noopener" class="font-semibold text-rosa underline">{{ '@'.$doador->instagram }}</a>
                                    @endif
                                </p>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</x-layout>
