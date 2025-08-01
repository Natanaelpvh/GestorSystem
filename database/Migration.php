<?php

declare(strict_types=1);

namespace Database;

use PDO;

/**
 * Classe base abstrata para todas as migrações do banco de dados.
 *
 * Define a estrutura que cada arquivo de migração deve seguir,
 * forçando a implementação dos métodos up() e down().
 */
abstract class Migration
{
    /** @var PDO A instância da conexão com o banco de dados. */
    protected PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    abstract public function up(): void;
    abstract public function down(): void;
}
