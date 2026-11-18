<?php

namespace Tests\Feature;

use App\Models\Edicao;
use App\Models\Lote;
use App\Support\Midia;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MidiaTest extends TestCase
{
    use RefreshDatabase;

    public function test_gera_webp_thumb_e_previa_do_whatsapp(): void
    {
        Storage::fake('public');

        $caminho = Midia::salvar(UploadedFile::fake()->image('foto.png', 2400, 3000), 'lotes');

        $this->assertStringEndsWith('/full.webp', $caminho);
        Storage::disk('public')->assertExists([$caminho, dirname($caminho).'/thumb.webp', dirname($caminho).'/og.jpg']);

        [$w] = getimagesizefromstring(Storage::disk('public')->get($caminho));
        [$tw] = getimagesizefromstring(Storage::disk('public')->get(dirname($caminho).'/thumb.webp'));
        [$ow, $oh] = getimagesizefromstring(Storage::disk('public')->get(dirname($caminho).'/og.jpg'));
        $this->assertSame([1600, 640, 1200, 630], [$w, $tw, $ow, $oh]);
    }

    public function test_trocar_imagem_apaga_a_antiga(): void
    {
        Storage::fake('public');
        $edicao = Edicao::create(['numero' => 16, 'titulo' => 'x', 'slug' => '2026', 'cidade' => 'x', 'uf' => 'RO', 'data_evento' => now(), 'ativa' => true]);

        $antiga = Midia::salvar(UploadedFile::fake()->image('a.jpg', 800, 800), 'lotes');
        $lote = Lote::create(['edicao_id' => $edicao->id, 'numero' => 1, 'nome' => 'Teste', 'imagem_path' => $antiga]);

        $nova = Midia::salvar(UploadedFile::fake()->image('b.jpg', 800, 800), 'lotes');
        $lote->update(['imagem_path' => $nova]);

        Storage::disk('public')->assertMissing($antiga);
        Storage::disk('public')->assertExists($nova);

        $lote->delete();
        Storage::disk('public')->assertMissing($nova);
    }
}
