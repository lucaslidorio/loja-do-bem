<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('edicoes', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('numero');              // 16
            $table->string('titulo');                            // 16º Leilão Direito de Viver
            $table->string('slug')->unique();                    // 2026
            $table->string('cidade');
            $table->char('uf', 2);
            $table->dateTime('data_evento');
            $table->string('local_nome')->nullable();            // Espaço Direito de Viver
            $table->string('local_endereco')->nullable();
            $table->string('maps_url', 500)->nullable();
            $table->text('convite_info')->nullable();            // preço, onde comprar
            $table->string('whatsapp', 20)->nullable();          // só números, com DDI: 5569...
            $table->string('instagram')->nullable();             // sem @
            $table->text('sobre')->nullable();                   // texto sobre a causa
            $table->decimal('meta_arrecadacao', 12, 2)->nullable();
            $table->boolean('ativa')->default(false)->index();   // a edição que aparece no site
            $table->boolean('resultado_publico')->default(false); // mostrar total arrecadado
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('edicoes');
    }
};
