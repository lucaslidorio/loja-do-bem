<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Interesse extends Model
{
    protected $table = 'interesses';

    public const UPDATED_AT = null;

    protected $fillable = ['lote_id', 'visitante'];

    public function lote(): BelongsTo
    {
        return $this->belongsTo(Lote::class);
    }
}
