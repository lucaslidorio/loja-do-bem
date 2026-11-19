@props(['lote', 'interessado' => false, 'filtravel' => true])
@php
    $busca = collect([$lote->numero, $lote->nome, $lote->doador?->nome, $lote->categoria?->nome])->filter()->implode(' ');
@endphp
<li @if ($filtravel)
        data-lote-item data-id="{{ $lote->getKey() }}" data-categoria="{{ $lote->categoria?->slug }}" data-busca="{{ $busca }}"
        x-show="mostra($el)"
    @endif
    {{ $attributes->class('group relative flex flex-col') }}>
    <a href="{{ $lote->url() }}" class="flex flex-1 flex-col rounded-[14px] focus-visible:outline-offset-4">
        <div @class(['relative overflow-hidden rounded-[14px] bg-papel-2', 'aspect-[4/5]' => $lote->imagem_path, 'aspect-square' => ! $lote->imagem_path])>
            @if ($src = $lote->imagemUrl('thumb'))
                {{-- Só a arte (card com texto): mostra inteira, sem cortar --}}
                <img src="{{ $src }}" alt="{{ $lote->nome }}" loading="lazy" decoding="async" width="640" height="800"
                    @class([
                        'size-full transition-transform duration-500 ease-[var(--ease-saida)] group-hover:scale-[1.035]',
                        'object-cover' => true,
                        'grayscale-[60%]' => $lote->arrematado(),
                    ])>
            @else
                <div class="flex size-full items-end bg-[linear-gradient(160deg,var(--color-papel-2),var(--color-linha))] p-4">
                    <span class="font-display text-7xl font-black text-cartao/90">{{ $lote->numero }}</span>
                </div>
            @endif

            @if ($lote->arrematado())
                <span class="absolute inset-x-3 top-1/2 -translate-y-1/2 -rotate-6 rounded-md border-[3px] border-mata bg-cartao/90 py-1.5 text-center font-display text-2xl font-black uppercase tracking-wide text-mata">
                    Arrematado
                </span>
            @elseif ($lote->destaque)
                <span class="absolute left-3 top-3 rounded-full bg-marinho px-2.5 py-1 text-[0.7rem] font-bold uppercase tracking-[0.06em] text-white">
                    {{ $lote->valor_imensuravel ? 'Peça única' : 'Destaque' }}
                </span>
            @endif
        </div>

        <div class="flex flex-1 flex-col pt-3 pr-1">
            <p class="font-display text-[1.05rem] font-bold uppercase text-rosa numeros">Lote {{ $lote->numero }}</p>
            <h3 class="mt-1 text-[0.98rem] font-semibold leading-snug text-tinta">{{ $lote->nome }}</h3>
            @if ($lote->doador)
                <p class="mt-1 text-[0.8rem] text-tinta-2">Doação de {{ $lote->doador->nome }}</p>
            @endif
            @if ($valor = $lote->valorTexto())
                <p class="mt-auto pt-2 text-sm font-semibold numeros {{ $lote->valor_imensuravel ? 'text-marinho' : 'text-tinta' }}">
                    @unless ($lote->valor_imensuravel)<span class="font-normal text-tinta-2">ref.</span>@endunless {{ $valor }}
                </p>
            @endif
        </div>
    </a>

    @unless ($lote->arrematado())
        <x-botao-interesse :lote="$lote" :ativo="$interessado" class="absolute right-2.5 top-2.5" />
    @endunless
</li>
