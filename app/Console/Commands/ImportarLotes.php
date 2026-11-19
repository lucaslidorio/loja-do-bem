<?php

namespace App\Console\Commands;

use App\Models\Categoria;
use App\Models\Doador;
use App\Models\Edicao;
use App\Models\Lote;
use App\Support\Midia;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

#[Signature('lotes:importar
    {--csv= : Planilha CSV (separada por ;) com numero;nome;categoria;doador;valor;destaque;publicado}
    {--artes= : Pasta com as artes LOTE-101.png, LOTE-102.jpg... (o "Baixar tudo" do Google Drive)}
    {--edicao= : Endereço da edição (ex.: 2026). Padrão: a edição atual}
    {--publicar : Publica no site os lotes criados só a partir das artes}')]
#[Description('Importa lotes de uma planilha CSV e/ou de uma pasta de artes (LOTE-NNN.png)')]
class ImportarLotes extends Command
{
    public function handle(): int
    {
        $edicao = $this->option('edicao')
            ? Edicao::where('slug', $this->option('edicao'))->first()
            : Edicao::atual();

        if (! $edicao) {
            $this->error('Nenhuma edição encontrada. Cadastre a edição no painel antes de importar.');

            return self::FAILURE;
        }

        if (! $this->option('csv') && ! $this->option('artes')) {
            $this->error('Informe --csv=arquivo.csv e/ou --artes=pasta.');

            return self::FAILURE;
        }

        $this->info("Importando para: {$edicao->titulo}");

        if ($csv = $this->option('csv')) {
            $this->importarCsv($edicao, $csv);
        }

        if ($pasta = $this->option('artes')) {
            $this->importarArtes($edicao, $pasta);
        }

        return self::SUCCESS;
    }

    private function importarCsv(Edicao $edicao, string $arquivo): void
    {
        if (! is_readable($arquivo)) {
            $this->error("Não consegui ler {$arquivo}");

            return;
        }

        $fp = fopen($arquivo, 'r');
        $cabecalho = array_map(fn ($c) => Str::slug(trim($c, "\u{FEFF} \t"), '_'), fgetcsv($fp, separator: ';', escape: ''));
        $n = 0;

        while (($linha = fgetcsv($fp, separator: ';', escape: '')) !== false) {
            if (count(array_filter($linha, 'strlen')) === 0) {
                continue;
            }

            $d = array_combine($cabecalho, array_pad($linha, count($cabecalho), ''));
            $numero = (int) ($d['numero'] ?? 0);

            if ($numero <= 0 || blank($d['nome'] ?? null)) {
                $this->warn('Linha ignorada (sem número ou nome): '.implode(';', $linha));

                continue;
            }

            $valor = trim((string) ($d['valor'] ?? ''));
            $imensuravel = Str::startsWith(Str::lower(Str::ascii($valor)), 'imensur');

            $lote = Lote::firstOrNew(['edicao_id' => $edicao->getKey(), 'numero' => $numero]);
            $lote->fill([
                'nome' => trim($d['nome']),
                'categoria_id' => $this->categoria($d['categoria'] ?? null)?->getKey(),
                'doador_id' => $this->doador($d['doador'] ?? null)?->getKey(),
                'valor_imensuravel' => $imensuravel,
                'valor_referencia' => $imensuravel || $valor === '' ? null : (float) str_replace(['R$', '.', ','], ['', '', '.'], $valor),
                'destaque' => in_array(Str::lower(trim($d['destaque'] ?? '')), ['sim', 's', '1', 'x'], true),
            ]);
            $publicado = Str::lower(Str::ascii(trim($d['publicado'] ?? '')));
            if (in_array($publicado, ['nao', 'n', '0'], true)) {
                $lote->publicado = false;
            } elseif (! $lote->exists || in_array($publicado, ['sim', 's', '1'], true)) {
                $lote->publicado = true;
            }
            $lote->save();
            $n++;
        }

        fclose($fp);
        $this->info("{$n} lotes importados da planilha.");
    }

    private function importarArtes(Edicao $edicao, string $pasta): void
    {
        $arquivos = glob(rtrim($pasta, '/').'/*.{png,PNG,jpg,JPG,jpeg,JPEG,webp,WEBP}', GLOB_BRACE) ?: [];

        if (! $arquivos) {
            $this->error("Nenhuma imagem encontrada em {$pasta}");

            return;
        }

        $barra = $this->output->createProgressBar(count($arquivos));
        $ok = 0;

        foreach ($arquivos as $arquivo) {
            $barra->advance();

            if (! preg_match('/(\d{2,4})/', basename($arquivo), $m)) {
                continue;
            }

            $numero = (int) $m[1];
            $lote = Lote::firstOrNew(['edicao_id' => $edicao->getKey(), 'numero' => $numero]);

            if (! $lote->exists) {
                $lote->nome = "Lote {$numero}";
                $lote->publicado = (bool) $this->option('publicar');
            }

            $lote->arte_path = Midia::salvar($arquivo, 'artes');
            $lote->save();
            $ok++;
        }

        $barra->finish();
        $this->newLine();
        $this->info("{$ok} artes importadas.");

        $semNome = Lote::where('edicao_id', $edicao->getKey())->where('nome', 'like', 'Lote %')->count();
        if ($semNome) {
            $this->warn("{$semNome} lotes foram criados só com a arte: complete o nome no painel.");
        }
    }

    private function categoria(?string $nome): ?Categoria
    {
        $nome = trim((string) $nome);

        return $nome === '' ? null : Categoria::firstOrCreate(['slug' => Str::slug($nome)], ['nome' => $nome]);
    }

    private function doador(?string $nome): ?Doador
    {
        $nome = trim((string) $nome);

        if ($nome === '') {
            return null;
        }

        return Doador::where('nome', $nome)->first() ?? Doador::create(['nome' => $nome]);
    }
}
