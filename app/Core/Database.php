<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;

/**
 * Classe Database para gerenciar a conexão com o banco de dados usando o padrão Singleton.
 *
 * Esta classe garante que exista apenas uma instância da conexão PDO durante
 * todo o ciclo de vida da requisição, economizando recursos e evitando múltiplas conexões.
 * A classe é final para impedir que seja estendida.
 */
final class Database
{
    /**
     * A única instância da classe PDO.
     *
     * @var PDO|null
     */
    private static ?PDO $instance = null;

    /**
     * O construtor é privado para prevenir a criação de uma nova instância
     * com o operador 'new' de fora desta classe.
     */
    private function __construct()
    {
    }

    /**
     * O método clone é privado para prevenir a clonagem da instância.
     */
    private function __clone()
    {
    }

    /**
     * Previne a desserialização da instância, o que violaria o padrão Singleton.
     *
     * @throws \Exception
     */
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize a singleton.");
    }

    /**
     * Obtém a instância única da conexão PDO.
     *
     * Se a instância ainda não existir, ela é criada na primeira chamada.
     * As credenciais e configurações são lidas a partir de variáveis de ambiente.
     *
     * @return PDO A instância do objeto PDO.
     * @throws PDOException Se a conexão com o banco de dados falhar.
     */
    public static function getInstance(): PDO
    {
        // Verifica se a instância já foi criada
        if (self::$instance === null) {
            // Carrega as configurações do banco de dados a partir do arquivo de configuração
            $config = require dirname(__DIR__, 2) . '/config/database.php';

            // Monta a Data Source Name (DSN) string
            $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";

            // Define as opções de conexão do PDO para segurança e eficiência
            $options = [
                // Lança exceções em caso de erros
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                // Define o modo de busca padrão para array associativo
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                // Desativa a emulação de prepared statements para maior segurança (prevenção de SQL Injection)
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                // Cria a nova instância do PDO
                self::$instance = new PDO($dsn, $config['user'], $config['pass'], $options);
            } catch (PDOException $e) {
                // Em caso de falha na conexão, lança uma exceção para ser tratada em um nível superior
                throw new PDOException($e->getMessage(), (int)$e->getCode());
            }
        }

        // Retorna a instância existente ou a recém-criada
        return self::$instance;
    }
}
