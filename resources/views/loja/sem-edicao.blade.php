<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Loja do Bem</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    @vite(['resources/css/app.css'])
</head>
<body class="flex min-h-dvh items-center bg-papel text-tinta">
    <main class="mx-auto max-w-xl px-6 py-16">
        <x-marca class="h-14 w-14" />
        <h1 class="mt-6 font-display text-6xl font-black uppercase text-marinho">A loja abre em breve</h1>
        <p class="mt-4 text-lg leading-relaxed text-tinta-2">
            Os lotes do próximo Leilão Direito de Viver ainda estão sendo cadastrados.
        </p>
        <p class="mt-8 text-sm text-tinta-2">
            Faz parte da organização? Entre no <a href="{{ url('/painel') }}" class="font-semibold text-rosa underline">painel</a>
            e cadastre a edição em Leilão → Edições, marcando "Edição atual".
        </p>
    </main>
</body>
</html>
