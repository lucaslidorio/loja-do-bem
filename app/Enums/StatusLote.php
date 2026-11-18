<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum StatusLote: string implements HasColor, HasLabel
{
    case Disponivel = 'disponivel';
    case Arrematado = 'arrematado';
    case Retirado = 'retirado';

    public function getLabel(): string
    {
        return match ($this) {
            self::Disponivel => 'Disponível',
            self::Arrematado => 'Arrematado',
            self::Retirado => 'Retirado do leilão',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Disponivel => 'gray',
            self::Arrematado => 'success',
            self::Retirado => 'danger',
        };
    }
}
