<?php

return [

    /*
    | Disco onde ficam as imagens dos lotes e logos dos doadores.
    | Em produção: "s3". Para testar na sua máquina: "public" (+ php artisan storage:link).
    |
    | No S3 os arquivos são gravados SEM ACL (buckets novos vêm com ACL desligada).
    | A leitura pública deve vir de uma bucket policy liberando GetObject no
    | prefixo "lotes/*" e "doadores/*". Veja o README.
    */
    'media_disk' => env('MEDIA_DISK', 's3'),

    // Tamanhos gerados a partir de cada imagem enviada
    'imagem' => [
        'full' => 1600,   // página do lote (webp)
        'thumb' => 640,   // vitrine (webp)
        'og' => [1200, 630], // prévia do WhatsApp/Facebook (jpg)
        'qualidade' => 82,
        'fundo_og' => '#f6f1ea',
    ],

    // Nome do cookie anônimo usado para contar os "tenho interesse"
    'cookie_visitante' => 'ldb_visitante',

];
