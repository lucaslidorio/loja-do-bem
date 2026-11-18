<?php

namespace App\Models;

use App\Support\Midia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Doador extends Model
{
    protected $table = 'doadores';

    protected $fillable = ['nome', 'slug', 'cidade', 'instagram', 'whatsapp', 'site', 'logo_path'];

    protected static function booted(): void
    {
        static::saving(function (Doador $doador) {
            if (blank($doador->slug)) {
                $base = Str::slug($doador->nome);
                $slug = $base;
                $i = 2;
                while (static::where('slug', $slug)->whereKeyNot($doador->getKey())->exists()) {
                    $slug = "{$base}-".$i++;
                }
                $doador->slug = $slug;
            }

            $doador->instagram = filled($doador->instagram) ? ltrim(trim($doador->instagram), '@') : null;
        });

        static::updated(function (Doador $doador) {
            if ($doador->wasChanged('logo_path') && $doador->getOriginal('logo_path')) {
                Midia::apagar($doador->getOriginal('logo_path'));
            }
        });

        static::deleted(fn (Doador $doador) => Midia::apagar($doador->logo_path));
    }

    public function lotes(): HasMany
    {
        return $this->hasMany(Lote::class);
    }

    public function logoUrl(): ?string
    {
        return Midia::url($this->logo_path, 'thumb');
    }

    public function instagramUrl(): ?string
    {
        return $this->instagram ? 'https://instagram.com/'.$this->instagram : null;
    }
}
