<?php

namespace Tests\Feature;

use App\Models\Edicao;
use App\Models\Lote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PainelTest extends TestCase
{
    use RefreshDatabase;

    public function test_painel_exige_login(): void
    {
        $this->get('/painel')->assertRedirect('/painel/login');
    }

    public function test_voluntario_ve_dashboard_e_lotes(): void
    {
        $edicao = Edicao::create(['numero' => 16, 'titulo' => '16º Leilão Direito de Viver', 'slug' => '2026', 'cidade' => 'Rolim de Moura', 'uf' => 'RO', 'data_evento' => now()->addWeek(), 'ativa' => true]);
        Lote::create(['edicao_id' => $edicao->id, 'numero' => 101, 'nome' => 'Caixa térmica 28L']);

        $user = User::create(['name' => 'Voluntário', 'email' => 'v@teste.local', 'password' => 'segredo-123']);

        $this->actingAs($user)->get('/painel')->assertOk(); // widgets carregam depois (lazy)
        $this->actingAs($user)->get('/painel/lotes')->assertOk()->assertSee('Caixa térmica 28L');
        $this->actingAs($user)->get('/painel/lotes/create')->assertOk();
        $this->actingAs($user)->get('/painel/edicoes/2026/edit')->assertOk();
    }
}
