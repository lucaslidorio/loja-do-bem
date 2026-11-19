<?php

namespace Tests\Feature;

use App\Models\Edicao;
use App\Models\Lote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImportarLotesTest extends TestCase
{
    use RefreshDatabase;

    public function test_importa_csv_e_artes(): void
    {
        Storage::fake('public');
        Edicao::create(['numero' => 16, 'titulo' => '16º', 'slug' => '2026', 'cidade' => 'Rolim de Moura', 'uf' => 'RO', 'data_evento' => now()->addWeek(), 'ativa' => true]);

        $csv = tempnam(sys_get_temp_dir(), 'csv');
        file_put_contents($csv, "numero;nome;categoria;doador;valor;destaque;publicado\n101;Caixa térmica;Casa e cozinha;Casa & Roça;170;;\n124;Chapéu autografado;Autografados;Asrolim;imensurável;sim;\n215;Item retido;;Hospital de Amor;;;não\n");

        $pasta = sys_get_temp_dir().'/artes-'.uniqid();
        mkdir($pasta);
        imagepng(imagecreatetruecolor(400, 500), "{$pasta}/LOTE-101.png");
        imagepng(imagecreatetruecolor(400, 500), "{$pasta}/LOTE-130.png");

        $this->artisan('lotes:importar', ['--csv' => $csv, '--artes' => $pasta])->assertSuccessful();

        $this->assertSame(4, Lote::count());
        $this->assertFalse(Lote::where('numero', 215)->first()->publicado);
        $this->assertEquals(170, Lote::where('numero', 101)->first()->valor_referencia);
        $this->assertNotNull(Lote::where('numero', 101)->first()->arte_path);

        $chapeu = Lote::where('numero', 124)->first();
        $this->assertTrue($chapeu->valor_imensuravel && $chapeu->destaque);
        $this->assertSame('Asrolim', $chapeu->doador->nome);

        $soArte = Lote::where('numero', 130)->first();
        $this->assertFalse($soArte->publicado); // criado só da arte: espera o nome no painel
    }
}
