<?php

namespace App\Models;

use App\Enums\StatusLote;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Edicao extends Model
{
    protected $table = 'edicoes';

    protected $fillable = [
        'numero', 'titulo', 'slug', 'cidade', 'uf', 'data_evento',
        'local_nome', 'local_endereco', 'maps_url', 'convite_info',
        'whatsapp', 'instagram', 'sobre', 'meta_arrecadacao',
        'ativa', 'resultado_publico',
    ];

    protected function casts(): array
    {
        return [
            'data_evento' => 'datetime',
            'meta_arrecadacao' => 'decimal:2',
            'ativa' => 'boolean',
            'resultado_publico' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        // Só uma edição fica ativa por vez
        // Apaga os lotes um a um para remover também as imagens do S3
        static::deleting(fn (Edicao $edicao) => $edicao->lotes()->each(fn (Lote $lote) => $lote->delete()));

        static::saved(function (Edicao $edicao) {
            if ($edicao->ativa) {
                static::whereKeyNot($edicao->getKey())->where('ativa', true)->update(['ativa' => false]);
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function lotes(): HasMany
    {
        return $this->hasMany(Lote::class);
    }

    public function scopeAtiva(Builder $query): void
    {
        $query->where('ativa', true);
    }

    public static function atual(): ?self
    {
        return static::ativa()->first() ?? static::latest('data_evento')->first();
    }

    public function totalArrecadado(): float
    {
        return (float) $this->lotes()
            ->where('status', StatusLote::Arrematado)
            ->sum('valor_arrematado');
    }

    public function jaAconteceu(): bool
    {
        return $this->data_evento->copy()->endOfDay()->isPast();
    }

    public function whatsappUrl(?string $texto = null): ?string
    {
        if (blank($this->whatsapp)) {
            return null;
        }

        return 'https://wa.me/'.preg_replace('/\D/', '', $this->whatsapp)
            .($texto ? '?text='.rawurlencode($texto) : '');
    }
}
