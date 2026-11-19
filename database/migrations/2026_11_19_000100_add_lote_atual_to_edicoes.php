<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Lote que está no telão agora (null = tela de espera)
        Schema::table('edicoes', function (Blueprint $table) {
            $table->foreignId('lote_atual_id')->nullable()->after('resultado_publico')
                ->constrained('lotes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('edicoes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('lote_atual_id');
        });
    }
};
