<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Classe Router para gerenciar o roteamento de requisições HTTP.
 *
 * Esta classe permite registrar rotas para os métodos GET e POST e despachar
 * a requisição para o controlador e método correspondentes.
 */
class Router
{
    /** @var self|null A instância singleton do Router. */
    private static ?self $instance = null;

    /**
     * O construtor é privado para forçar o uso do getInstance().
     */
    private function __construct() {}

    /**
     * Armazena todas as rotas registradas.
     *
     * A estrutura é um array associativo onde a chave principal é o método HTTP
     * (e.g., 'GET', 'POST') e o valor é um array de URIs e suas ações.
     *
     * @var array
     */
    protected array $routes = [];

    /**
     * Obtém a instância singleton do Router.
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // Os métodos get() e post() foram movidos para a classe Route para uma API mais limpa,
    // mas o método principal para adicionar rotas permanece aqui.
    /**
     * Registra uma rota para o método GET.
     *
     * @param string $uri A URI da rota.
     * @param array  $action Um array contendo o nome da classe do controlador e o método. Ex: [HomeController::class, 'index'].
     * @return void
     */

    /**
     * Adiciona uma rota à coleção de rotas.
     *
     * @param string $method O método HTTP (GET, POST, etc.).
     * @param string          $uri    A URI da rota.
     * @param array|\Closure  $action O controlador e método associados ou uma função anônima.
     * @return void
     */
    public function addRoute(string $method, string $uri, array|\Closure $action): void
    {
        $this->routes[$method][$uri] = $action;
    }

    /**
     * Despacha a requisição para a rota correspondente.
     *
     * Encontra a rota que corresponde à URI e ao método da requisição, instancia
     * o controlador e chama o método associado. Se nenhuma rota for encontrada,
     * envia uma resposta 404 Not Found.
     *
     * @param string $uri A URI da requisição atual.
     * @param string $method O método HTTP da requisição atual.
     * @return void
     */
    public function dispatch(string $uri, string $method): void
    {
        $requestUri = rtrim(parse_url($uri, PHP_URL_PATH) ?: '/', '/');
        $method = strtoupper($method);

        // Lógica para lidar com a execução em subdiretórios.
        // Obtém o caminho do script de entrada (ex: /GestorSystem/public/index.php)
        $scriptName = $_SERVER['SCRIPT_NAME'];
        // Obtém o diretório base da aplicação (ex: /GestorSystem/public)
        $basePath = dirname($scriptName);

        // Se a aplicação não estiver na raiz do servidor, remove o caminho base da URI da requisição.
        if ($basePath !== '/' && $basePath !== '\\' && str_starts_with($requestUri, $basePath)) {
            $requestUri = substr($requestUri, strlen($basePath));
        }

        $requestUri = empty($requestUri) ? '/' : $requestUri;

        foreach ($this->routes[$method] ?? [] as $routeUri => $action) {
            // Converte a rota URI em um padrão regex
            $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[a-zA-Z0-9_]+)', $routeUri);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $requestUri, $matches)) {
                // Remove as chaves numéricas, mantendo apenas as nomeadas (parâmetros)
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                // Verifica se a ação é uma Closure (função anônima)
                if ($action instanceof \Closure) {
                    call_user_func_array($action, $params);
                    return;
                }

                // Se não for uma Closure, assume que é um array [Controller, 'metodo']
                if (is_array($action)) {
                    [$controller, $method] = $action;
                    if (class_exists($controller) && method_exists($controller, $method)) {
                        $controllerInstance = new $controller();
                        call_user_func_array([$controllerInstance, $method], $params);
                        return;
                    }
                }
            }
        }

        // Se nenhuma rota for encontrada, retorna um erro 404.
        http_response_code(404);
        // Em um cenário ideal, você renderizaria uma view de erro.
        // (new Controller())->view('errors.404');
        echo "Página não encontrada (404)";
        exit;
    }
}
