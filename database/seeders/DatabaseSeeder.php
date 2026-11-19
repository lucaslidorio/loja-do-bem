<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Edicao;
use Illuminate\Database\Seeder;

/**
 * Dados iniciais: categorias e a 16ª edição.
 * Rode uma vez: php artisan db:seed
 * Os lotes entram pelo painel ou por: php artisan lotes:importar --csv=database/dados/lotes-2026.csv
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = [
            'Autografados e peças únicas',
            'Casa e cozinha',
            'Cama, mesa e banho',
            'Relógios e joias',
            'Moda e acessórios',
            'Beleza',
            'Bebidas',
            'Religiosos',
            'Esporte e lazer',
            'Agro e ferramentas',
            'Eletrônicos',
            'Livros',
        ];

        // Só preenche banco vazio: pode rodar sempre (o Docker roda a cada subida)
        // sem desfazer o que a organização mudou pelo painel.
        if (Categoria::doesntExist()) {
            foreach ($categorias as $ordem => $nome) {
                Categoria::create(['nome' => $nome, 'ordem' => $ordem]);
            }
        }

        if (Edicao::exists()) {
            return;
        }

        Edicao::create([
            'slug' => '2026',
            'numero' => 16,
            'titulo' => '16º Leilão Direito de Viver',
            'cidade' => 'Rolim de Moura',
            'uf' => 'RO',
            'data_evento' => '2026-12-06 11:00:00',
            'local_nome' => 'Espaço Direito de Viver',
            'ativa' => true,
        ]);
    }
}
