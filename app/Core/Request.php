<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Classe Request para lidar com os dados de entrada da requisição HTTP.
 *
 * Fornece métodos estáticos para acessar e sanitizar de forma segura
 * os dados das superglobais $_GET, $_POST e $_FILES.
 */
class Request
{
    /**
     * Obtém um valor do array $_GET de forma segura.
     *
     * @param string $key A chave do parâmetro a ser recuperado.
     * @param mixed  $default O valor padrão a ser retornado se a chave não existir.
     * @return mixed O valor sanitizado do parâmetro GET ou o valor padrão.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $value = filter_input(INPUT_GET, $key, FILTER_SANITIZE_SPECIAL_CHARS);
        return ($value === false || $value === null) ? $default : $value;
    }

    /**
     * Obtém um valor do array $_POST de forma segura.
     *
     * @param string $key A chave do parâmetro a ser recuperado.
     * @param mixed  $default O valor padrão a ser retornado se a chave não existir.
     * @return mixed O valor sanitizado do parâmetro POST ou o valor padrão.
     */
    public static function post(string $key, mixed $default = null): mixed
    {
        $value = filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS);
        return ($value === false || $value === null) ? $default : $value;
    }

    /**
     * Obtém informações de um arquivo enviado via $_FILES.
     *
     * @param string $key A chave do arquivo no array $_FILES.
     * @return array|null Um array com as informações do arquivo ou null se não existir.
     */
    public static function file(string $key): ?array
    {
        return $_FILES[$key] ?? null;
    }

    /**
     * Obtém todos os dados de entrada (GET e POST) como um único array.
     *
     * Os dados são sanitizados. Em caso de chaves duplicadas, os valores
     * de POST sobrescrevem os de GET.
     *
     * @return array Um array com todos os dados da requisição.
     */
    public static function all(): array
    {
        $post = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS) ?: [];
        $get = filter_input_array(INPUT_GET, FILTER_SANITIZE_SPECIAL_CHARS) ?: [];

        // Mescla os arrays, com os dados de POST tendo precedência.
        return array_merge($get, $post);
    }

    /**
     * Obtém o método da requisição HTTP, considerando a simulação de métodos.
     *
     * Verifica se existe um campo '_method' no corpo da requisição POST para
     * simular métodos como PUT, PATCH ou DELETE, que não são suportados
     * nativamente por formulários HTML.
     *
     * @return string O método da requisição (ex: 'GET', 'POST', 'PUT', 'DELETE').
     */
    public static function method(): string
    {
        // Se o campo _method existir no POST, use-o.
        if (isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
            if (in_array($method, ['PUT', 'PATCH', 'DELETE'])) {
                return $method;
            }
        }

        // Caso contrário, use o método real do servidor.
        return $_SERVER['REQUEST_METHOD'];
    }
}