# Loja do Bem · Leilão Direito de Viver

Vitrine on-line dos lotes do Leilão Direito de Viver, em prol do Hospital de Amor.
A venda continua presencial: o site serve para divulgar os lotes, ser compartilhado no WhatsApp
e mostrar para a organização quais lotes estão mais procurados.

- **Loja:** `https://seudominio/`, com a vitrine, a página de cada lote e a lista de doadores
- **Painel:** `https://seudominio/painel`, onde os voluntários cadastram lotes, doadores e edições

## O que tem

**Na loja**
- Vitrine com busca (ignora acentos), filtro por categoria e "Minha lista"
- Destaques no topo (peças únicas e autografadas)
- Página de cada lote com link próprio: `/2026/lote/124-chapeu-...`. A arte do lote vira a prévia no WhatsApp.
- Botões de compartilhar (menu nativo do celular ou WhatsApp) e copiar link
- Coração "tenho interesse": não pede login, usa um cookie anônimo
- Convite com data, local e contagem regressiva; página de doadores; página 404
- Depois do leilão: selo "Arrematado" e total arrecadado (se você ligar isso na edição)

**No painel**
- Login para voluntários (menu Sistema → Acessos)
- Lotes: número sugerido automaticamente, categoria e doador criados ali mesmo, foto e arte
- As imagens são convertidas sozinhas para WebP (vitrine 640px, página 1600px, prévia 1200×630) e enviadas ao S3
- Na lista de lotes: botão para registrar o arremate e o valor, ligar ou desligar a publicação no site, filtros
- Painel inicial com total de lotes, lotes sem imagem, interesses, arrecadação e os lotes mais desejados
- Edições: cada ano é uma edição; a marcada como "atual" é a que aparece na loja

## Instalação na Hostinger (hospedagem compartilhada)

### 1. Antes de subir (na sua máquina)
```sh
npm install
npm run build      # gera public/build (o servidor não roda npm)
```

### 2. No hPanel
- **PHP 8.3 ou mais novo**, com as extensões `intl`, `gd`, `fileinfo` e `pdo_mysql`
- Em configurações do PHP: `upload_max_filesize` e `post_max_size` de pelo menos **16M**
- Crie o banco MySQL e o usuário
- Aponte o domínio ou subdomínio para a pasta `public` do projeto, como você já faz nas outras aplicações

### 3. Pelo SSH
```sh
cd ~/domains/SEU-DOMINIO           # ou a pasta onde você colocou o projeto
composer install --no-dev --optimize-autoloader
cp .env.example .env               # e edite: APP_URL, DB_*, AWS_*
php artisan key:generate
php artisan migrate --seed --force # tabelas + categorias + 16ª edição
php artisan make:filament-user     # seu acesso ao painel
php artisan optimize
php artisan filament:optimize
```

No `.env` de produção:
```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://seudominio
MEDIA_DISK=s3
```

### 4. Importar os lotes do Google Drive
1. No Drive, abra a pasta **LOJA DO BEM - 2026** e clique em **Baixar tudo**. Descompacte o arquivo.
2. Envie a pasta para o servidor (por exemplo, `~/artes-2026`).
3. Rode:
```sh
php artisan lotes:importar --csv=database/dados/lotes-2026.csv --artes=~/artes-2026
```
- O CSV `database/dados/lotes-2026.csv` já traz os **lotes 101 a 150**, com nome, categoria, doador, valor e destaque, transcritos das artes. A coluna `revisar` marca os nomes de doador que estavam pequenos demais para ler com certeza: confira no painel.
- Cada arte `LOTE-NNN.png` vai para o lote NNN. Lotes que existem só na arte (151 em diante) são criados **despublicados**, com o nome "Lote NNN". Complete o nome no painel e ligue "No site".
- Pode rodar o comando de novo sem problema: ele atualiza o que já existe.

## Configurar o S3 (AWS)

1. **Bucket:** crie na região `sa-east-1` (São Paulo). Deixe "Object Ownership" como *ACLs disabled*.
   Em "Block public access", desmarque só as duas opções sobre **bucket policies**.
2. **Bucket policy** (leitura pública só das imagens):
```json
{
  "Version": "2012-10-17",
  "Statement": [{
    "Sid": "LeituraPublicaImagens",
    "Effect": "Allow",
    "Principal": "*",
    "Action": "s3:GetObject",
    "Resource": [
      "arn:aws:s3:::SEU-BUCKET/lotes/*",
      "arn:aws:s3:::SEU-BUCKET/artes/*",
      "arn:aws:s3:::SEU-BUCKET/doadores/*"
    ]
  }]
}
```
3. **CORS**, para a prévia das imagens no painel:
```json
[{ "AllowedMethods": ["GET", "HEAD"], "AllowedOrigins": ["https://seudominio"], "AllowedHeaders": ["*"] }]
```
4. **Usuário IAM** só para o site, com esta política (gere a chave de acesso e coloque no `.env`):
```json
{
  "Version": "2012-10-17",
  "Statement": [
    { "Effect": "Allow", "Action": ["s3:PutObject", "s3:GetObject", "s3:DeleteObject"], "Resource": "arn:aws:s3:::SEU-BUCKET/*" },
    { "Effect": "Allow", "Action": "s3:ListBucket", "Resource": "arn:aws:s3:::SEU-BUCKET" }
  ]
}
```
5. `.env`: `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_DEFAULT_REGION=sa-east-1`, `AWS_BUCKET`.
   Se usar CloudFront, coloque o domínio dele em `AWS_URL`.

> Os arquivos são gravados **sem ACL**, de propósito: buckets novos recusam ACL. A leitura pública vem da policy acima.

## Docker (recomendado para testar no seu computador)

Precisa só do Docker (no Windows: Docker Desktop com integração WSL ligada).
Clone o projeto **dentro do WSL** (ex.: `~/projetos`), não em `/mnt/c`, porque fica muito mais rápido.

```sh
git clone https://github.com/lucaslidorio/loja-do-bem.git
cd loja-do-bem
docker compose up -d --build
docker compose logs -f app        # acompanhe a primeira subida (composer install, migrations)
```

Na primeira vez, o container cria o `.env` a partir do `.env.docker`, instala o Composer, roda as migrations e o seed.
Quando aparecer `» Pronto`:

- Loja: http://localhost:8080
- Painel: http://localhost:8080/painel. Crie seu acesso com:
  `docker compose exec -u www-data app php artisan make:filament-user`
- MySQL (DBeaver/HeidiSQL): `localhost:3307`, usuário `loja`, senha `loja`

**Front-end com recarga automática** (enquanto mexe no visual):
```sh
docker compose --profile front up -d node
```
Para voltar ao CSS compilado: `docker compose stop node && rm -f public/hot`.
Antes de subir para a Hostinger, rode `docker compose run --rm node npm run build` e faça o commit de `public/build`.

**Importar os lotes** (coloque a pasta baixada do Drive dentro do projeto, ex.: `artes-2026/`, que já está no .gitignore):
```sh
docker compose exec -u www-data app php artisan lotes:importar --csv=database/dados/lotes-2026.csv --artes=artes-2026
```

**Testar o S3 local (MinIO)**, igual à produção:
```sh
docker compose --profile s3 up -d
# no .env: MEDIA_DISK=s3   (as outras variáveis AWS_* já vêm prontas no .env.docker)
docker compose exec -u www-data app php artisan config:clear
```
Console do MinIO: http://localhost:9001 (usuário `loja`, senha `loja-senha-local`).

**Outros comandos úteis**
```sh
docker compose exec -u www-data app php artisan test     # testes
docker compose exec -u www-data app bash                  # terminal dentro do container
docker compose down                                       # para tudo (o banco fica salvo)
docker compose down -v                                    # para e APAGA o banco
```

Se seu usuário no WSL não tiver UID 1000 (veja com `id -u`), rode `UID=$(id -u) GID=$(id -g) docker compose up -d --build`.

## Rodar na sua máquina sem Docker
```sh
composer install
cp .env.example .env
# no .env: DB_CONNECTION=sqlite (apague as linhas DB_HOST... ) e MEDIA_DISK=public
touch database/database.sqlite
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan make:filament-user
npm install && npm run dev
php artisan serve
```
Testes: `php artisan test`

## Próximos passos sugeridos
- QR Code impresso ao lado de cada lote no dia do leilão, levando à página do lote
- "Modo telão": tela cheia com o lote da vez, para projetar durante o leilão
- Página de resultado com o total arrecadado, para postar depois do evento

---
Feito por voluntários. Toda a renda do leilão vai para o Hospital de Amor.
