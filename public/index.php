<?php

declare(strict_types=1);

/**
 * Ponto de Entrada da Aplicação (Front Controller).
 *
 * Todas as requisições são direcionadas para este arquivo.
 */

// Carrega o autoloader do Composer para que as classes sejam encontradas.
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Carrega as variáveis de ambiente do arquivo .env (ex: credenciais do banco).
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

// Instancia e executa a aplicação.
(new App\Core\App())->run();

