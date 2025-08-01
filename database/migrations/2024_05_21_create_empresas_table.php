<?php

declare(strict_types=1);

use Database\Migration;

/**
 * Migração para criar a tabela 'empresas'.
 */
class CreateEmpresasTable extends Migration
{
    /**
     * Executa a migração para cima (cria a tabela).
     */
    public function up(): void
    {
        $sql = "
        CREATE TABLE IF NOT EXISTS empresas (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(191) NOT NULL,
            cnpj VARCHAR(18) NOT NULL UNIQUE,
            email VARCHAR(191) NOT NULL UNIQUE,
            telefone VARCHAR(20) NULL,
            endereco VARCHAR(191) NULL,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL
        );";
        $this->pdo->exec($sql);
    }

    /**
     * Executa a migração para baixo (remove a tabela).
     */
    public function down(): void
    {
        $sql = "DROP TABLE IF EXISTS empresas;";
        $this->pdo->exec($sql);
    }
}
