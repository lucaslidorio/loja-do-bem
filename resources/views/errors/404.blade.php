<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Página não encontrada · Loja do Bem</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    @vite(['resources/css/app.css'])
</head>
<body class="flex min-h-dvh items-center bg-papel text-tinta">
    <main class="mx-auto max-w-xl px-6 py-16">
        <x-marca class="h-14 w-14" />
        <h1 class="mt-6 font-display text-6xl font-black uppercase text-marinho">Esse lote não está aqui</h1>
        <p class="mt-4 text-lg leading-relaxed text-tinta-2">
            Pode ser que ele tenha saído do catálogo ou que o endereço esteja diferente.
            Os outros lotes continuam esperando o seu lance.
        </p>
        <a href="{{ url('/') }}" class="mt-8 inline-flex h-12 items-center rounded-full bg-rosa px-6 font-semibold text-white hover:bg-rosa-2">Voltar para a loja</a>
    </main>
</body>
</html>
