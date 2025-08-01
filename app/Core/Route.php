<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Classe de fachada (Facade) para o registro de rotas.
 *
 * Fornece uma API estática e limpa para definir rotas (GET, POST, etc.),
 * delegando o registro para a instância singleton do Router.
 * Isso permite uma sintaxe mais expressiva no arquivo de rotas, como Route::get(...).
 */
class Route
{
    /**
     * Registra uma rota para o método GET.
     *
     * @param string          $uri    A URI da rota.
     * @param array|\Closure  $action A ação a ser executada (controlador e método, ou uma Closure).
     */
    public static function get(string $uri, array|\Closure $action): void
    {
        Router::getInstance()->addRoute('GET', $uri, $action);
    }

    /**
     * Registra uma rota para o método POST.
     *
     * @param string          $uri    A URI da rota.
     * @param array|\Closure  $action A ação a ser executada (controlador e método, ou uma Closure).
     */
    public static function post(string $uri, array|\Closure $action): void
    {
        Router::getInstance()->addRoute('POST', $uri, $action);
    }

    /**
     * Registra uma rota para o método PUT.
     *
     * @param string          $uri    A URI da rota.
     * @param array|\Closure  $action A ação a ser executada (controlador e método, ou uma Closure).
     */
    public static function put(string $uri, array|\Closure $action): void
    {
        Router::getInstance()->addRoute('PUT', $uri, $action);
    }

    /**
     * Registra uma rota para o método PATCH.
     *
     * @param string          $uri    A URI da rota.
     * @param array|\Closure  $action A ação a ser executada (controlador e método, ou uma Closure).
     */
    public static function patch(string $uri, array|\Closure $action): void
    {
        Router::getInstance()->addRoute('PATCH', $uri, $action);
    }

    /**
     * Registra uma rota para o método DELETE.
     *
     * @param string          $uri    A URI da rota.
     * @param array|\Closure  $action A ação a ser executada (controlador e método, ou uma Closure).
     */
    public static function delete(string $uri, array|\Closure $action): void
    {
        Router::getInstance()->addRoute('DELETE', $uri, $action);
    }
}
