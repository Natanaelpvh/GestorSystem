<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Classe Session para gerenciar a sessão do usuário.
 *
 * Fornece uma interface orientada a objetos para interagir com a superglobal $_SESSION,
 * garantindo que a sessão seja iniciada automaticamente e fornecendo métodos
 * para definir, obter, verificar, remover e destruir dados da sessão.
 */
class Session
{
    /**
     * Construtor da classe Session.
     *
     * Inicia a sessão se ela ainda não estiver ativa.
     */
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Define um valor na sessão.
     *
     * @param string $key A chave sob a qual o valor será armazenado.
     * @param mixed $value O valor a ser armazenado.
     * @return void
     */
    public function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Obtém um valor da sessão.
     *
     * @param string $key A chave do valor a ser recuperado.
     * @param mixed $default O valor padrão a ser retornado se a chave não existir.
     * @return mixed O valor da sessão ou o valor padrão.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Verifica se uma chave existe na sessão.
     *
     * @param string $key A chave a ser verificada.
     * @return bool True se a chave existir, false caso contrário.
     */
    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    /**
     * Remove uma chave e seu valor da sessão.
     *
     * @param string $key A chave a ser removida.
     * @return void
     */
    public function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /**
     * Destrói todos os dados registrados em uma sessão.
     *
     * @return void
     */
    public function destroy(): void
    {
        // Limpa o array $_SESSION
        $_SESSION = [];
        // Destrói a sessão
        session_destroy();
    }
}