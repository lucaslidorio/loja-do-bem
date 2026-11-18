@props(['lote', 'ativo' => false, 'grande' => false])
<button type="button"
    x-data="interesse({ url: @js(route('interesse', $lote)), ativo: @js($ativo), total: @js($lote->interesses_count) })"
    data-lote="{{ $lote->getKey() }}"
    @click.prevent.stop="alternar()"
    @interesse-alterado.window="if ($event.detail.id == $el.dataset.lote && $event.target !== $el) { ativo = $event.detail.ativo }"
    :aria-pressed="ativo.toString()"
    aria-pressed="{{ $ativo ? 'true' : 'false' }}"
    :aria-label="ativo ? 'Remover da minha lista' : 'Tenho interesse neste lote'"
    aria-label="{{ $ativo ? 'Remover da minha lista' : 'Tenho interesse neste lote' }}"
    {{ $attributes->class([
        'group/coracao inline-flex items-center justify-center gap-2 font-semibold transition-colors duration-200',
        'h-12 rounded-full border-2 px-5 text-[0.95rem]' => $grande,
        'size-11 rounded-full bg-cartao/95 shadow-[0_2px_8px_-2px_rgb(27_29_54/0.25)]' => ! $grande,
    ]) }}
    @if ($grande)
        :class="ativo ? 'border-rosa bg-rosa text-white' : 'border-tinta/15 bg-cartao text-tinta hover:border-rosa hover:text-rosa'"
    @else
        :class="ativo ? 'text-rosa' : 'text-tinta/70 hover:text-rosa'"
    @endif
>
    <span x-ref="icone" class="relative inline-flex">
        <x-icone nome="heart" cheio class="size-[1.35rem]" x-show="ativo" x-cloak />
        <x-icone nome="heart" class="size-[1.35rem]" x-show="!ativo" />
    </span>
    @if ($grande)
        <span x-text="ativo ? 'Está na sua lista' : 'Tenho interesse'">{{ $ativo ? 'Está na sua lista' : 'Tenho interesse' }}</span>
    @endif
</button>
