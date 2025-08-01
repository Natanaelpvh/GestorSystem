<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Classe principal da aplicação.
 *
 * Esta classe é responsável por inicializar o sistema, carregar as rotas
 * e despachar a requisição para o router.
 */
class App
{
    /**
     * Executa a aplicação.
     *
     * Este é o ponto de entrada principal. Ele instancia o Router,
     * carrega as definições de rotas do arquivo web.php e, em seguida,
     * instrui o router a despachar a requisição HTTP atual.
     *
     * @return void
     */
    public function run(): void
    {
        // Carrega as definições de rota do usuário
        require_once dirname(__DIR__, 2) . '/routes/web.php';

        $router = Router::getInstance();
        $router->dispatch($_SERVER['REQUEST_URI'], Request::method());
    }
}