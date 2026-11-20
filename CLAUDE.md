# Loja do Bem

Vitrine on-line dos lotes do **Leilão Direito de Viver** (Rolim de Moura – RO), evento voluntário
cuja renda vai para o Hospital de Amor. Não há venda on-line: os lances são presenciais, no dia do leilão.
O site serve para divulgar os lotes e engajar (compartilhar no WhatsApp, marcar "tenho interesse").

## Stack

- Laravel 13, PHP 8.3+, MySQL (produção) / SQLite (testes)
- Painel: Filament 5 em `/painel` (`app/Filament`)
- Loja pública: Blade + Tailwind 4 + Alpine (`resources/views/loja`, `resources/views/components`, `resources/js/app.js`)
- Imagens: S3 (`MEDIA_DISK=s3`), convertidas para WebP por `App\Support\Midia` (Intervention Image 4, driver GD)
- Hospedagem: Hostinger compartilhada, deploy pela integração Git (webhook na `main`); quando precisa de composer/migrate/caches, o Lucas roda `bash deploy/pos-deploy.sh --forcar` no SSH (sem Cron). Sem Node no servidor: `npm run build` é feito localmente e `public/build` vai versionado. `.env` e o `.htaccess` da raiz (modelo em `deploy/hostinger.htaccess`) existem só no servidor.

## Conceitos

- **Edição** (`Edicao`): um leilão por ano. O `slug` é o ano (ex.: `2026`) e aparece na URL. Só uma fica `ativa`; ela é a página inicial.
- **Lote** (`Lote`): pertence a uma edição; URL `/{ano}/lote/{numero}-{slug}`. Tem `imagem_path` (foto) e `arte_path` (card de divulgação, usado na prévia do WhatsApp).
- **Categoria** e **Doador** valem para todas as edições.
- **Telão**: `edicoes.lote_atual_id` é o lote projetado; `/{ano}/telao` (público, consulta `/{ano}/telao/estado` a cada 2s) e o controle em `/organizacao/telao` (login).
- **Etiquetas** (`/organizacao/etiquetas`) com QR Code via `App\Support\QrCode` (bacon/bacon-qr-code, SVG).
- **Resultado** (`/{ano}/resultado`): público só com `resultado_publico`; logado vê prévia.
- **Interesse**: o coração da loja. Visitante anônimo identificado por cookie (`IdentificaVisitante`), contado em `lotes.interesses_count`.
- Imagens: `Midia::salvar()` grava `{pasta}/{uuid}/full.webp`, `thumb.webp` e `og.jpg`; no banco fica só o `full.webp`. Grava sem ACL (buckets S3 novos não aceitam); leitura pública via bucket policy.

## Comandos

```sh
php artisan migrate --seed                 # tabelas + categorias + 16ª edição
php artisan make:filament-user             # cria acesso ao painel
php artisan lotes:importar --csv=database/dados/lotes-2026.csv --artes=/caminho/LOJA_DO_BEM
php artisan test                           # testes (SQLite em memória)
npm run build                              # compila CSS/JS (rode antes de subir)
```

## Design

A identidade vem das artes do evento: rosa "Loja do Bem" (`--color-rosa`), azul-marinho "Viver" (`--color-marinho`),
papel claro de catálogo (`--color-papel`). Títulos em Big Shoulders Display, texto em Figtree (fontes servidas pelo próprio site, via npm/fontsource).
Tokens em `resources/css/app.css`. Ao mexer no visual, use a skill **impeccable** e respeite:
nada de eyebrow/kicker acima de títulos, nada de texto em gradiente, ícones só do componente `<x-icone>` (Heroicons),
animação só onde tem função (o coração, os números da contagem regressiva, a troca de lote e o carimbo no telão, o total subindo) e `prefers-reduced-motion` sempre respeitado.

## Convenções

- Código e textos da interface em português do Brasil.
- Textos do site falam com o público de Rolim de Moura: simples, diretos, sem jargão.
