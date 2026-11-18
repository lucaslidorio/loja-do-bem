<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('edicao_id')->constrained('edicoes')->cascadeOnDelete();
            $table->unsignedInteger('numero');                    // 101
            $table->string('nome');
            $table->string('slug');
            $table->text('descricao')->nullable();
            $table->foreignId('categoria_id')->nullable()->constrained('categorias')->nullOnDelete();
            $table->foreignId('doador_id')->nullable()->constrained('doadores')->nullOnDelete();
            $table->decimal('valor_referencia', 10, 2)->nullable();
            $table->boolean('valor_imensuravel')->default(false); // peças autografadas etc.
            $table->boolean('destaque')->default(false)->index();
            $table->string('imagem_path')->nullable();            // lotes/{uuid}/full.webp
            $table->string('arte_path')->nullable();              // arte de divulgação (opcional)
            $table->string('status', 20)->default('disponivel')->index(); // disponivel | arrematado | retirado
            $table->decimal('valor_arrematado', 10, 2)->nullable();
            $table->boolean('publicado')->default(true)->index();
            $table->unsignedInteger('interesses_count')->default(0);
            $table->timestamps();

            // a URL usa número + slug (/2026/lote/101-caixa-termica), então o slug pode repetir
            $table->unique(['edicao_id', 'numero']);
        });

        Schema::create('interesses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lote_id')->constrained('lotes')->cascadeOnDelete();
            $table->char('visitante', 64);                        // hash do cookie anônimo
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['lote_id', 'visitante']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interesses');
        Schema::dropIfExists('lotes');
    }
};
