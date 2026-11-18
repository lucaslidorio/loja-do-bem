<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\ImageInterface;

/**
 * Converte as imagens enviadas no painel para WebP em tamanhos prontos
 * e grava no disco de mídia (S3 em produção).
 *
 * Cada envio vira uma pasta: {pasta}/{uuid}/full.webp, thumb.webp e og.jpg.
 * No banco guardamos só o caminho do full.webp; os outros saem por convenção.
 */
class Midia
{
    public static function disco(): string
    {
        return config('loja.media_disk', 's3');
    }

    /**
     * Processa um upload e devolve o caminho do full.webp.
     */
    public static function salvar(UploadedFile|string $arquivo, string $pasta, bool $comOg = true): string
    {
        $cfg = config('loja.imagem');
        $origem = $arquivo instanceof UploadedFile ? $arquivo->getRealPath() : $arquivo;

        $manager = ImageManager::usingDriver(GdDriver::class);
        $base = trim($pasta, '/').'/'.Str::uuid();

        $ler = fn (): ImageInterface => $manager->decodePath($origem)->orient();

        self::gravar("{$base}/full.webp", (string) $ler()->scaleDown(width: $cfg['full'])
            ->encode(new WebpEncoder($cfg['qualidade'])), 'image/webp');

        self::gravar("{$base}/thumb.webp", (string) $ler()->scaleDown(width: $cfg['thumb'])
            ->encode(new WebpEncoder($cfg['qualidade'] - 4)), 'image/webp');

        if ($comOg) {
            [$w, $h] = $cfg['og'];
            self::gravar("{$base}/og.jpg", (string) $ler()->contain($w, $h, $cfg['fundo_og'])
                ->encode(new JpegEncoder(85)), 'image/jpeg');
        }

        return "{$base}/full.webp";
    }

    /**
     * URL pública de uma variante: full | thumb | og.
     */
    public static function url(?string $caminho, string $variante = 'full'): ?string
    {
        if (blank($caminho)) {
            return null;
        }

        return Storage::disk(self::disco())->url(self::variante($caminho, $variante));
    }

    public static function variante(string $caminho, string $variante): string
    {
        if (! str_ends_with($caminho, '/full.webp')) {
            return $caminho; // arquivo antigo/externo: usa como está
        }

        return match ($variante) {
            'thumb' => dirname($caminho).'/thumb.webp',
            'og' => dirname($caminho).'/og.jpg',
            default => $caminho,
        };
    }

    /**
     * Apaga a pasta inteira de um envio (todas as variantes).
     */
    public static function apagar(?string $caminho): void
    {
        if (blank($caminho)) {
            return;
        }

        $disco = Storage::disk(self::disco());

        if (str_ends_with($caminho, '/full.webp')) {
            $disco->deleteDirectory(dirname($caminho));
        } else {
            $disco->delete($caminho);
        }
    }

    private static function gravar(string $caminho, string $conteudo, string $mime): void
    {
        // Sem "visibility": buckets S3 novos não aceitam ACL.
        // A leitura pública vem da bucket policy (ver README).
        Storage::disk(self::disco())->put($caminho, $conteudo, [
            'ContentType' => $mime,
            'CacheControl' => 'public, max-age=31536000, immutable',
        ]);
    }
}
