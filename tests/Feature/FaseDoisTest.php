<?php

namespace Tests\Feature;

use App\Enums\StatusLote;
use App\Models\Edicao;
use App\Models\Lote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FaseDoisTest extends TestCase
{
    use RefreshDatabase;

    private Edicao $edicao;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->edicao = Edicao::create([
            'numero' => 16, 'titulo' => '16º Leilão Direito de Viver', 'slug' => '2026',
            'cidade' => 'Rolim de Moura', 'uf' => 'RO', 'data_evento' => now()->addDays(5), 'ativa' => true,
        ]);

        foreach ([101 => 'Caixa térmica', 102 => 'Chaleira', 103 => 'Sela'] as $n => $nome) {
            Lote::create(['edicao_id' => $this->edicao->id, 'numero' => $n, 'nome' => $nome, 'valor_referencia' => 100]);
        }
        Lote::create(['edicao_id' => $this->edicao->id, 'numero' => 104, 'nome' => 'Escondido', 'publicado' => false]);

        $this->user = User::create(['name' => 'V', 'email' => 'v@t.local', 'password' => 'segredo-123']);
    }

    public function test_paginas_da_organizacao_exigem_login(): void
    {
        $this->get('/organizacao/etiquetas')->assertRedirect('/painel/login');
        $this->get('/organizacao/telao')->assertRedirect('/painel/login');
        $this->post('/organizacao/telao/mostrar', ['direcao' => 'proximo'])->assertRedirect('/painel/login');
    }

    public function test_etiquetas_com_qr_code_dos_lotes_publicados(): void
    {
        $this->actingAs($this->user)->get('/organizacao/etiquetas')
            ->assertOk()
            ->assertSee('Caixa térmica')
            ->assertDontSee('Escondido')
            ->assertSee('<svg', false)
            ->assertSee('3 etiquetas');

        $this->actingAs($this->user)->get('/organizacao/etiquetas?de=102&ate=102')
            ->assertSee('Chaleira')->assertDontSee('Caixa térmica');
    }

    public function test_controle_do_telao_navega_e_registra_arremate(): void
    {
        $this->actingAs($this->user);

        $this->post('/organizacao/telao/mostrar', ['direcao' => 'proximo']);
        $this->assertSame(101, $this->edicao->fresh()->loteAtual->numero);

        $this->post('/organizacao/telao/mostrar', ['direcao' => 'proximo']);
        $this->assertSame(102, $this->edicao->fresh()->loteAtual->numero);

        $this->post('/organizacao/telao/mostrar', ['numero' => 103]);
        $this->assertSame(103, $this->edicao->fresh()->loteAtual->numero);

        // 104 não está publicado: "próximo" não passa dele
        $this->post('/organizacao/telao/mostrar', ['direcao' => 'proximo'])->assertSessionHas('aviso');

        $this->post('/organizacao/telao/arrematar', ['valor' => 750])->assertSessionHas('ok');
        $this->assertSame(StatusLote::Arrematado, Lote::where('numero', 103)->first()->status);

        $this->getJson('/2026/telao/estado')
            ->assertJsonPath('lote.numero', 103)
            ->assertJsonPath('lote.arrematado', true)
            ->assertJsonPath('arrecadado', 750)
            ->assertJsonPath('arrematados', 1)
            ->assertJsonPath('totalLotes', 3);

        $this->post('/organizacao/telao/desfazer');
        $this->assertSame(StatusLote::Disponivel, Lote::where('numero', 103)->first()->status);

        $this->post('/organizacao/telao/mostrar'); // tela de espera
        $this->assertNull($this->edicao->fresh()->lote_atual_id);
        $this->get('/organizacao/telao')->assertOk()->assertSee('Tela de espera');
    }

    public function test_telao_publico_abre_sem_login(): void
    {
        $this->get('/2026/telao')->assertOk()->assertSee('Aponte a câmera e veja a loja');
        $this->getJson('/2026/telao/estado')->assertOk()->assertJsonPath('lote', null);
    }

    public function test_resultado_so_publico_quando_liberado(): void
    {
        Lote::where('numero', 101)->first()->update(['status' => StatusLote::Arrematado, 'valor_arrematado' => 1200]);

        $this->get('/2026/resultado')->assertNotFound();
        $this->actingAs($this->user)->get('/2026/resultado')->assertOk()->assertSee('Prévia');

        $this->edicao->update(['resultado_publico' => true]);
        auth()->logout();

        $this->get('/2026/resultado')
            ->assertOk()
            ->assertDontSee('Prévia')
            ->assertSee('1.200,00')
            ->assertSee('Os maiores lances');
    }

    public function test_apagar_edicao_com_lote_no_telao(): void
    {
        $this->edicao->update(['lote_atual_id' => Lote::first()->id]);
        $this->edicao->delete();

        $this->assertSame(0, Lote::count());
    }
}
