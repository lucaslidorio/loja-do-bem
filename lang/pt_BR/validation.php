<?php

// Mensagens de validação em português (as mais usadas no painel)
return [
    'accepted' => 'O campo :attribute deve ser aceito.',
    'alpha_dash' => 'O campo :attribute só pode ter letras, números, hífen e sublinhado.',
    'array' => 'O campo :attribute deve ser uma lista.',
    'boolean' => 'O campo :attribute deve ser verdadeiro ou falso.',
    'confirmed' => 'A confirmação de :attribute não confere.',
    'date' => 'O campo :attribute não é uma data válida.',
    'email' => 'Informe um e-mail válido em :attribute.',
    'exists' => 'O valor de :attribute não foi encontrado.',
    'file' => 'O campo :attribute deve ser um arquivo.',
    'image' => 'O campo :attribute deve ser uma imagem.',
    'integer' => 'O campo :attribute deve ser um número inteiro.',
    'max' => [
        'numeric' => 'O campo :attribute não pode ser maior que :max.',
        'file' => 'O arquivo :attribute não pode ter mais que :max KB.',
        'string' => 'O campo :attribute não pode ter mais que :max caracteres.',
        'array' => 'O campo :attribute não pode ter mais que :max itens.',
    ],
    'mimes' => 'O campo :attribute deve ser um arquivo do tipo: :values.',
    'mimetypes' => 'O campo :attribute deve ser um arquivo do tipo: :values.',
    'min' => [
        'numeric' => 'O campo :attribute deve ser pelo menos :min.',
        'file' => 'O arquivo :attribute deve ter pelo menos :min KB.',
        'string' => 'O campo :attribute deve ter pelo menos :min caracteres.',
        'array' => 'O campo :attribute deve ter pelo menos :min itens.',
    ],
    'numeric' => 'O campo :attribute deve ser um número.',
    'regex' => 'O formato de :attribute é inválido.',
    'required' => 'O campo :attribute é obrigatório.',
    'size' => [
        'string' => 'O campo :attribute deve ter :size caracteres.',
    ],
    'string' => 'O campo :attribute deve ser um texto.',
    'unique' => 'Este :attribute já está em uso.',
    'uploaded' => 'Não foi possível enviar :attribute. Tente uma imagem menor.',
    'url' => 'O campo :attribute deve ser um link válido (com https://).',

    'attributes' => [
        'numero' => 'número',
        'nome' => 'nome',
        'email' => 'e-mail',
        'password' => 'senha',
        'slug' => 'endereço no site',
        'uf' => 'UF',
        'data_evento' => 'data e hora',
        'valor_arrematado' => 'valor arrematado',
        'valor_referencia' => 'valor de referência',
    ],
];
