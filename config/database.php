<?php

declare(strict_types=1);

/**
 * Arquivo de configuração do banco de dados.
 *
 * Retorna um array com as credenciais e configurações para a conexão,
 * lendo os valores a partir das variáveis de ambiente através da função helper env().
 */
return [
    'host'    => env('DB_HOST', '127.0.0.1'),
    'dbname'  => env('DB_DATABASE', 'nome_do_banco'),
    'user'    => env('DB_USERNAME', 'root'),
    'pass'    => env('DB_PASSWORD', ''),
    'charset' => env('DB_CHARSET', 'utf8mb4'),
];