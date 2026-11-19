<?php

namespace App\Models;

use App\Enums\StatusLote;
use App\Support\Midia;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Number;
use Illuminate\Support\Str;

class Lote extends Model
{
    protected $table = 'lotes';

    protected $fillable = [
        'edicao_id', 'numero', 'nome', 'slug', 'descricao', 'categoria_id', 'doador_id',
        'valor_referencia', 'valor_imensuravel', 'destaque', 'imagem_path', 'arte_path',
        'status', 'valor_arrematado', 'publicado',
    ];

    protected function casts(): array
    {
        return [
            'numero' => 'integer',
            'valor_referencia' => 'decimal:2',
            'valor_arrematado' => 'decimal:2',
            'valor_imensuravel' => 'boolean',
            'destaque' => 'boolean',
            'publicado' => 'boolean',
            'status' => StatusLote::class,
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Lote $lote) {
            $lote->slug = Str::slug($lote->nome) ?: 'lote';

            if ($lote->status !== StatusLote::Arrematado) {
                $lote->valor_arrematado = null;
            }
        });

        // Troca de imagem: apaga a antiga só depois de salvar com sucesso
        static::updated(function (Lote $lote) {
            foreach (['imagem_path', 'arte_path'] as $campo) {
                if ($lote->wasChanged($campo) && $lote->getOriginal($campo)) {
                    Midia::apagar($lote->getOriginal($campo));
                }
            }
        });

        static::deleted(function (Lote $lote) {
            Midia::apagar($lote->imagem_path);
            Midia::apagar($lote->arte_path);
        });
    }

    public function edicao(): BelongsTo
    {
        return $this->belongsTo(Edicao::class);
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    public function doador(): BelongsTo
    {
        return $this->belongsTo(Doador::class);
    }

    public function interesses(): HasMany
    {
        return $this->hasMany(Interesse::class);
    }

    public function scopePublicado(Builder $query): void
    {
        $query->where('publicado', true);
    }

    /** Parâmetro da URL: 101-caixa-termica-28l */
    public function referencia(): string
    {
        return $this->numero.'-'.$this->slug;
    }

    public function url(): string
    {
        return route('lote', [$this->edicao, $this->referencia()]);
    }

    public function imagemUrl(string $variante = 'full'): ?string
    {
        return Midia::url($this->imagem_path ?: $this->arte_path, $variante);
    }

    /** Imagem da prévia no WhatsApp: a arte de divulgação, se houver. */
    public function ogImagemUrl(): ?string
    {
        return Midia::url($this->arte_path ?: $this->imagem_path, 'og');
    }

    public function arteUrl(): ?string
    {
        return Midia::url($this->arte_path, 'full');
    }

    public function valorTexto(): ?string
    {
        if ($this->valor_imensuravel) {
            return 'Valor imensurável';
        }

        return $this->valor_referencia ? self::reais($this->valor_referencia) : null;
    }

    public function arrematado(): bool
    {
        return $this->status === StatusLote::Arrematado;
    }

    public static function reais(float|string|null $valor): string
    {
        return Number::currency((float) $valor, in: 'BRL', locale: 'pt_BR');
    }

    public function textoCompartilhar(bool $comUrl = true): string
    {
        $edicao = $this->edicao;

        return "Lote {$this->numero}: {$this->nome}\n"
            ."Vai a leilão no {$edicao->titulo}, dia {$edicao->data_evento->format('d/m')} em {$edicao->cidade}. "
            .'Toda a renda vai para o Hospital de Amor Amazônia.'
            .($comUrl ? "\n{$this->url()}" : '');
    }
}
