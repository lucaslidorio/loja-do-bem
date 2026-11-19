<?php

namespace Tests\Feature;

use App\Enums\StatusLote;
use App\Models\Categoria;
use App\Models\Doador;
use App\Models\Edicao;
use App\Models\Lote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LojaTest extends TestCase
{
    use RefreshDatabase;

    private Edicao $edicao;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        $this->edicao = Edicao::create([
            'numero' => 16, 'titulo' => '16º Leilão Direito de Viver', 'slug' => '2026',
            'cidade' => 'Rolim de Moura', 'uf' => 'RO', 'data_evento' => now()->addDays(10)->setTime(11, 0),
            'ativa' => true,
        ]);
    }

    private function lote(array $dados = []): Lote
    {
        return Lote::create(array_merge([
            'edicao_id' => $this->edicao->id,
            'numero' => 101,
            'nome' => 'Caixa térmica 28L',
            'valor_referencia' => 170,
        ], $dados));
    }

    public function test_vitrine_mostra_so_lotes_publicados(): void
    {
        $this->lote();
        $this->lote(['numero' => 102, 'nome' => 'Lote escondido', 'publicado' => false]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Caixa térmica 28L')
            ->assertDontSee('Lote escondido')
            ->assertSee('170,00');
    }

    public function test_pagina_do_lote_e_redirecionamento_do_slug(): void
    {
        $lote = $this->lote();

        $this->get('/2026/lote/101-caixa-termica-28l')
            ->assertOk()
            ->assertSee('og:image', false)
            ->assertSee('Como arrematar');

        $this->get('/2026/lote/101')->assertRedirect($lote->url());
        $this->get('/2026/lote/999-nada')->assertNotFound();
    }

    public function test_lote_nao_publicado_da_404(): void
    {
        $this->lote(['publicado' => false]);
        $this->get('/2026/lote/101-caixa-termica-28l')->assertNotFound();
    }

    public function test_marcar_e_desmarcar_interesse(): void
    {
        $lote = $this->lote();
        $visitante = '6d9a1f5e-2b1c-4c3d-8e9f-0a1b2c3d4e5f';

        $this->withCredentials()->withCookie('ldb_visitante', $visitante)->postJson("/interesse/{$lote->id}")->assertJson(['interessado' => true, 'total' => 1]);
        $this->withCredentials()->withCookie('ldb_visitante', $visitante)->postJson("/interesse/{$lote->id}")->assertJson(['interessado' => false, 'total' => 0]);
        $this->assertSame(0, $lote->fresh()->interesses_count);
    }

    public function test_visitantes_diferentes_somam_interesses(): void
    {
        $lote = $this->lote();

        $this->withCredentials()->withCookie('ldb_visitante', '6d9a1f5e-2b1c-4c3d-8e9f-0a1b2c3d4e5f')->postJson("/interesse/{$lote->id}");
        $this->withCredentials()->withCookie('ldb_visitante', '7e0b2a6f-3c2d-4d4e-9f0a-1b2c3d4e5f6a')->postJson("/interesse/{$lote->id}");

        $this->assertSame(2, $lote->fresh()->interesses_count);
    }

    public function test_pagina_de_doadores(): void
    {
        $doador = Doador::create(['nome' => 'Café Canindé', 'instagram' => '@cafecaninde']);
        $this->lote(['doador_id' => $doador->id]);

        $this->assertSame('cafecaninde', $doador->fresh()->instagram);
        $this->get('/2026/doadores')->assertOk()->assertSee('Café Canindé')->assertDontSee('Caixa térmica 28L');
    }

    public function test_so_uma_edicao_fica_ativa(): void
    {
        $nova = Edicao::create([
            'numero' => 17, 'titulo' => '17º Leilão', 'slug' => '2027', 'cidade' => 'Rolim de Moura',
            'uf' => 'RO', 'data_evento' => '2027-12-05 11:00', 'ativa' => true,
        ]);

        $this->assertFalse($this->edicao->fresh()->ativa);
        $this->assertTrue(Edicao::atual()->is($nova));
        $this->get('/2026')->assertOk(); // edição antiga continua acessível
        $this->get('/2027')->assertRedirect('/');
    }

    public function test_total_arrecadado_so_conta_arrematados(): void
    {
        $this->lote(['status' => StatusLote::Arrematado, 'valor_arrematado' => 300]);
        $this->lote(['numero' => 102, 'status' => StatusLote::Disponivel, 'valor_arrematado' => 999]);

        $this->assertSame(300.0, $this->edicao->totalArrecadado());
        $this->assertNull(Lote::where('numero', 102)->first()->valor_arrematado);
    }

    public function test_sem_edicao_mostra_aviso_em_vez_de_404(): void
    {
        $this->edicao->delete();

        $this->get('/')->assertStatus(503)->assertSee('A loja abre em breve');
    }

    public function test_seeder_pode_rodar_varias_vezes_sem_duplicar(): void
    {
        $this->edicao->delete();

        $this->seed();
        $this->seed();

        $this->assertSame(1, Edicao::count());
        $this->assertSame(12, Categoria::count());
        $this->get('/')->assertOk();
    }

    public function test_filtro_por_categoria_aparece_na_vitrine(): void
    {
        $cat = Categoria::create(['nome' => 'Bebidas']);
        $this->lote(['categoria_id' => $cat->id]);

        $this->get('/')->assertSee('data-categoria="bebidas"', false);
    }
}
