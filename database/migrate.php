<?php

/**
 * Script de linha de comando para executar as migrações do banco de dados.
 *
 * Uso: php database/migrate.php
 */

echo "================================\n";
echo "  GESTORSYSTEM MIGRATION RUNNER \n";
echo "================================\n\n";

// 1. Bootstrap do ambiente da aplicação
require_once dirname(__DIR__) . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

// 2. Conexão com o Banco de Dados
try {
    $config = require dirname(__DIR__) . '/config/database.php';
    $dbName = $config['dbname'];
    echo "Attempting to connect to database '{$dbName}'...\n";

    $dsn = "mysql:host={$config['host']};dbname={$dbName};charset={$config['charset']}";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, $config['user'], $config['pass'], $options);
    echo "Database connection successful.\n\n";
} catch (PDOException $e) {
    die("ERROR: Could not connect to the database. Please check your .env configuration and ensure the database '{$dbName}' exists.\nDetails: " . $e->getMessage() . "\n");
}

// 3. Encontrar e Executar as Migrações
$migrationsPath = __DIR__ . '/migrations';
$migrationFiles = glob($migrationsPath . '/*.php');

if (empty($migrationFiles)) {
    echo "No migration files found.\n";
    exit;
}

echo "Starting migrations...\n";

foreach ($migrationFiles as $file) {
    require_once $file;

    $filename = pathinfo($file, PATHINFO_FILENAME);
    // Converte o nome do arquivo (ex: 2024_05_21_create_empresas_table) para um nome de classe (ex: CreateEmpresasTable)
    $className = implode('', array_map('ucfirst', explode('_', preg_replace('/^\d{4}_\d{2}_\d{2}_/', '', $filename))));

    if (class_exists($className)) {
        echo "  - Migrating: {$className}\n";
        try {
            $migration = new $className($pdo);
            $migration->up();
            echo "  - Migrated:  {$className}\n";
        } catch (Exception $e) {
            echo "  - ERROR migrating {$className}: " . $e->getMessage() . "\n";
        }
    }
}

echo "\nAll migrations completed.\n";
