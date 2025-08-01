<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Classe Response para gerenciar e enviar respostas HTTP.
 *
 * Fornece métodos para definir o código de status HTTP, redirecionar
 * o cliente e enviar respostas no formato JSON.
 */
class Response
{
    /**
     * Define o código de status da resposta HTTP.
     *
     * @param int $code O código de status HTTP (ex: 200, 404, 500).
     * @return self Retorna a própria instância para permitir method chaining.
     */
    public function setStatus(int $code): self
    {
        http_response_code($code);
        return $this;
    }

    /**
     * Redireciona o navegador para uma nova URL.
     *
     * Envia um cabeçalho de 'Location' e termina a execução do script.
     *
     * @param string $url A URL para a qual o usuário será redirecionado.
     * @return void
     */
    public function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }

    /**
     * Envia uma resposta no formato JSON.
     *
     * Define o cabeçalho 'Content-Type' para 'application/json',
     * codifica os dados e termina a execução do script.
     *
     * @param array $data Os dados a serem codificados em JSON.
     * @return void
     */
    public function json(array $data): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }
}