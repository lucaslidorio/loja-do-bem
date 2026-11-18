<?php

namespace App\Filament\Support;

use App\Support\Midia;
use Filament\Forms\Components\FileUpload;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * Campo de imagem padrão do painel: recebe a foto, converte para WebP
 * (full, thumb e prévia do WhatsApp) e grava no S3.
 */
class CampoImagem
{
    public static function make(string $campo, string $pasta, bool $comOg = true): FileUpload
    {
        return FileUpload::make($campo)
            ->image()
            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->maxSize(10 * 1024) // limite do upload temporário do Livewire é 12 MB
            ->disk(fn () => Midia::disco())
            ->visibility('public')
            ->imagePreviewHeight('220')
            ->saveUploadedFileUsing(
                fn (TemporaryUploadedFile $file): string => Midia::salvar($file, $pasta, $comOg)
            );
    }
}
