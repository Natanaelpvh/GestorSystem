<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Classe Controller base abstrata.
 *
 * Esta classe serve como a fundação para todos os outros controllers da aplicação.
 * Ela fornece métodos auxiliares comuns, como renderizar uma view ou redirecionar
 * o usuário. É declarada como 'abstract' pois não deve ser instanciada diretamente.
 */
abstract class Controller
{
    /** @var Template O motor de template. */
    protected Template $template;

    /** @var Response O objeto de resposta. */
    protected Response $response;

    /**
     * Construtor do Controller.
     *
     * Inicializa as dependências comuns como Template e Response.
     */
    public function __construct()
    {
        $this->template = new Template();
        $this->response = new Response();
    }

    /**
     * Renderiza um arquivo de view usando o motor de template.
     *
     * @param string $path O caminho para o arquivo da view (notação de ponto, ex: 'empresa.index').
     * @param array  $data Um array associativo de dados a serem passados para a view.
     * @return void
     */
    protected function view(string $path, array $data = []): void
    {
        // Adiciona dinamicamente o caminho base para todas as views, tornando os links portáteis.
        $data['basePath'] = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
        echo $this->template->render($path, $data);
    }

    /**
     * Redireciona o navegador para uma nova URL usando a classe Response.
     *
     * @param string $url A URL para a qual o usuário será redirecionado.
     * @return void
     */
    protected function redirect(string $url): void
    {
        // Constrói a URL completa para o redirecionamento, garantindo portabilidade.
        $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
        $this->response->redirect($basePath . $url);
    }
}