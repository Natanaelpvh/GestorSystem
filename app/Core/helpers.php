<?php

declare(strict_types=1);

if (!function_exists('env')) {
    /**
     * Obtém o valor de uma variável de ambiente de um arquivo .env.
     *
     * Lê o arquivo .env na raiz do projeto apenas uma vez e armazena os valores
     * em cache (em uma variável estática) para chamadas subsequentes.
     *
     * @param string $key A chave da variável a ser recuperada.
     * @param mixed|null $default O valor padrão a ser retornado se a chave não for encontrada.
     * @return mixed
     */
    function env(string $key, mixed $default = null): mixed
    {
        static $env = null;

        if ($env === null) {
            $path = dirname(__DIR__, 2) . '/.env';
            $env = [];
            if (is_readable($path)) {
                $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach ($lines as $line) {
                    // Ignora comentários
                    if (str_starts_with(trim($line), '#')) {
                        continue;
                    }

                    // Divide a linha em nome e valor
                    if (str_contains($line, '=')) {
                        list($name, $value) = explode('=', $line, 2);
                        $env[trim($name)] = trim($value);
                    }
                }
            }
        }

        return $env[$key] ?? $default;
    }
}