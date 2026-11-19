<?php

namespace App\Support;

use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\Fill;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class QrCode
{
    /**
     * SVG do QR Code, pronto para colocar direto no HTML.
     * Cor padrão: azul-marinho da identidade (lê bem impresso em preto e branco).
     */
    public static function svg(string $conteudo, int $tamanho = 240, string $cor = '#1b1d36', string $fundo = '#ffffff'): string
    {
        $style = new RendererStyle(
            $tamanho,
            margin: 1,
            fill: Fill::uniformColor(self::rgb($fundo), self::rgb($cor)),
        );

        $svg = (new Writer(new ImageRenderer($style, new SvgImageBackEnd)))->writeString($conteudo);

        // remove a declaração XML para poder embutir no HTML
        return trim(preg_replace('/^<\?xml[^>]*\?>/', '', $svg));
    }

    private static function rgb(string $hex): Rgb
    {
        $hex = ltrim($hex, '#');

        return new Rgb(hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2)));
    }
}
