<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Categoria extends Model
{
    protected $table = 'categorias';

    protected $fillable = ['nome', 'slug', 'ordem'];

    protected static function booted(): void
    {
        static::saving(function (Categoria $categoria) {
            $categoria->slug = filled($categoria->slug) ? Str::slug($categoria->slug) : Str::slug($categoria->nome);
        });
    }

    public function lotes(): HasMany
    {
        return $this->hasMany(Lote::class);
    }
}
