<?php

/**
 * GESTORSYSTEM - Migration & Scaffolding Tool
 *
 * Script de linha de comando para executar e criar migrações, models e controllers.
 * Este script é o coração do sistema de banco de dados do GestorSystem.
 *
 * =================================[ COMANDOS DE USO ]=================================
 *
 * Para executar migrações pendentes:
 * php database/migrate.php run
 *
 * Para criar um novo Model, Migration e Controller (padrão avançado):
 * php database/migrate.php make:NomeDoModel
 *
 * Para sincronizar o Model com o banco de dados (adicionar novas colunas):
 * php database/migrate.php update:NomeDoModel
 *
 * Para reverter o último lote de migrações:
 * php database/migrate.php rollback
 *
 * =====================================================================================
 *
 * @version 4.2 (Final Pattern)
 * @author Natanael S. de Oliveira <rnh.personalizados@gmail.com>
 */

// Bloco de Helpers para saída no console com cores. Melhora a experiência do dev.
final class Console
{
    public static function log(string $message): void { echo $message . PHP_EOL; }
    public static function success(string $message): void { echo "\033[32m" . $message . "\033[0m" . PHP_EOL; }
    public static function error(string $message): void { echo "\033[31m" . $message . "\033[0m" . PHP_EOL; }
    public static function warning(string $message): void { echo "\033[33m" . $message . "\033[0m" . PHP_EOL; }
    public static function info(string $message): void { echo "\033[36m" . $message . "\033[0m" . PHP_EOL; }
    public static function header(string $title): void {
        $line = str_repeat('=', strlen($title) + 4);
        self::info("\n" . $line . "\n  " . $title . "  \n" . $line . "\n");
    }
}

// Roteador de Comandos
$command = $argv[1] ?? 'run';

if (strpos($command, 'make:') === 0) {
    handleMakeCommand(substr($command, 5));
} elseif (strpos($command, 'update:') === 0) {
    handleUpdateCommand(substr($command, 7));
} elseif ($command === 'run') {
    runMigrations();
} elseif ($command === 'rollback') {
    handleRollbackCommand();
} else {
    Console::error("Comando desconhecido: {$command}");
    Console::log("Uso: php database/migrate.php [run|make:ModelName|update:ModelName|rollback]");
    exit(1);
}

function handleUpdateCommand(string $modelName): void
{
    Console::header("GESTORSYSTEM - SCHEMA UPDATER");
    if (empty($modelName) || !preg_match('/^[A-Z][a-zA-Z0-9]*$/', $modelName)) {
        Console::error("Erro: Nome de model inválido. Use PascalCase (ex: Produto, Usuario).");
        exit(1);
    }
    $modelFile = dirname(__DIR__) . "/app/Models/{$modelName}.php";
    if (!file_exists($modelFile)) {
        Console::error("Erro: Model '{$modelName}' não encontrado em '{$modelFile}'.");
        exit(1);
    }
    Console::log("Analisando model '{$modelName}'...");
    $pdo = getDbConnection();
    $tableName = strtolower($modelName) . 's';
    if (!tableExists($pdo, $tableName)) {
        Console::error("Erro: Tabela '{$tableName}' não existe. Crie-a com 'make:{$modelName}' e 'run' primeiro.");
        exit(1);
    }
    
    $baseModelPath = dirname(__DIR__) . "/app/Models/Model.php";
    if(file_exists($baseModelPath)) require_once $baseModelPath;
    
    require_once $modelFile;
    $reflection = new ReflectionClass("App\\Models\\{$modelName}");
    $tableColumns = getTableColumns($pdo, $tableName);
    $orderedPropertyNames = getOrderedPublicPropertyNamesFromFile($modelFile);
    $reflectionProperties = [];
    foreach ($reflection->getProperties(ReflectionProperty::IS_PUBLIC) as $prop) {
        $reflectionProperties[$prop->getName()] = $prop;
    }
    $newColumns = [];
    $ignoredProps = ['id', 'created_at', 'updated_at'];
    foreach ($orderedPropertyNames as $propName) {
        if (in_array($propName, $ignoredProps) || in_array($propName, $tableColumns)) {
            continue;
        }
        if (isset($reflectionProperties[$propName])) {
            $property = $reflectionProperties[$propName];
            $newColumns[$propName] = mapPropertyToSql($property);
        }
    }
    if (empty($newColumns)) {
        Console::success("Tabela '{$tableName}' já está sincronizada com o model '{$modelName}'. Nenhuma alteração necessária.");
        exit;
    }
    generateAlterMigration($modelName, $newColumns);
    Console::success("\nMigração de alteração criada com sucesso!");
    Console::info("Execute 'php database/migrate.php run' para aplicar as mudanças.");
}

function handleMakeCommand(string $modelName): void
{
    Console::header("GESTORSYSTEM - FILE GENERATOR");

    if (empty($modelName) || !preg_match('/^[A-Z][a-zA-Z0-9]*$/', $modelName)) {
        Console::error("Erro: Nome de model inválido. Use PascalCase (ex: Produto, Usuario).");
        exit(1);
    }

    $vars = [
        'modelName'      => $modelName,
        'modelNameLower' => strtolower($modelName),
        'tableName'      => strtolower($modelName) . 's',
    ];

    generateModel($vars);
    generateMigration($vars);
    generateController($vars);

    Console::success("\nArquivos gerados com sucesso!");
    Console::info("1. Verifique o arquivo de migração e adicione/remova colunas se necessário.");
    Console::info("2. Execute 'php database/migrate.php run' para criar a tabela.");
}

// PADRÃO FINAL: Gerador de Model que cria todos os métodos CRUD.
/**
 * Gera o arquivo do Model com base no padrão Empresa.php.
 */
function generateModel(array $vars): void
{
    $modelName = $vars['modelName'];
    $tableName = $vars['tableName'];
    $modelDir = dirname(__DIR__) . "/app/Models";
    if (!is_dir($modelDir)) mkdir($modelDir, 0775, true);
    $modelPath = "{$modelDir}/{$modelName}.php";

    if (file_exists($modelPath)) {
        Console::warning("Aviso: Model 'app/Models/{$modelName}.php' já existe. Nenhuma ação foi tomada.");
        return;
    }

    $template = <<<PHP
<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

/**
 * Model para a tabela '{$tableName}'.
 *
 * Esta classe representa um(a) {$modelName} no banco de dados e fornece métodos
 * para interagir com a tabela '{$tableName}' (CRUD - Create, Read, Update, Delete).
 */
class {$modelName} extends Model
{
    /**
     * O nome da tabela do banco de dados associada a este model.
     * @var string
     */
    protected string \$table = '{$tableName}';

    /**
     * Os atributos que podem ser atribuídos em massa.
     * @var array
     */
    protected array \$fillable = [
        // TODO: Adicione os campos que podem ser preenchidos via formulário.
        // 'nome', 'email', etc.
    ];

    // As propriedades públicas devem espelhar as colunas da tabela.
    public ?int \$id = null;
    
    // TODO: Adicione aqui as outras propriedades/colunas da sua tabela.
    // public ?string \$nome = null;

    public ?string \$created_at = null;
    public ?string \$updated_at = null;

    /**
     * Busca um(a) {$modelName} pelo seu ID.
     * @param int \$id O ID do recurso.
     * @return array|null Retorna os dados ou null se não for encontrado.
     */
    public function find(int \$id): ?array
    {
        \$stmt = \$this->db->prepare("SELECT * FROM {\$this->table} WHERE id = :id");
        \$stmt->bindParam(':id', \$id, PDO::PARAM_INT);
        \$stmt->execute();
        \$result = \$stmt->fetch(PDO::FETCH_ASSOC);

        return \$result ?: null;
    }

    /**
     * Retorna todos os recursos da tabela.
     * @return array Um array com todos os registros.
     */
    public function all(): array
    {
        \$stmt = \$this->db->query("SELECT * FROM {\$this->table} ORDER BY id DESC");
        return \$stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Insere um novo registro no banco de dados.
     * @param array \$data Um array associativo com os dados a serem criados.
     * @return bool Retorna true em caso de sucesso, false em caso de falha.
     */
    public function create(array \$data): bool
    {
        \$data = \$this->filterFillable(\$data);

        if (!isset(\$data['created_at'])) {
            \$data['created_at'] = date('Y-m-d H:i:s');
        }
        if (!isset(\$data['updated_at'])) {
            \$data['updated_at'] = date('Y-m-d H:i:s');
        }

        \$columns = implode(', ', array_keys(\$data));
        \$placeholders = ':' . implode(', :', array_keys(\$data));

        \$sql = "INSERT INTO {\$this->table} ({\$columns}) VALUES ({\$placeholders})";

        \$stmt = \$this->db->prepare(\$sql);
        return \$stmt->execute(\$data);
    }

    /**
     * Atualiza os dados de um registro existente.
     * @param int \$id O ID do registro a ser atualizado.
     * @param array \$data Um array associativo com os dados a serem atualizados.
     * @return bool Retorna true em caso de sucesso, false em caso de falha.
     */
    public function update(int \$id, array \$data): bool
    {
        \$data = \$this->filterFillable(\$data);

        if (!isset(\$data['updated_at'])) {
            \$data['updated_at'] = date('Y-m-d H:i:s');
        }

        \$setClauses = [];
        foreach (array_keys(\$data) as \$key) {
            \$setClauses[] = "{\$key} = :{\$key}";
        }
        \$setSql = implode(', ', \$setClauses);

        \$sql = "UPDATE {\$this->table} SET {\$setSql} WHERE id = :id";

        \$stmt = \$this->db->prepare(\$sql);
        \$data['id'] = \$id;

        return \$stmt->execute(\$data);
    }

    /**
     * Exclui um registro do banco de dados.
     * @param int \$id O ID do registro a ser excluído.
     * @return bool Retorna true em caso de sucesso, false em caso de falha.
     */
    public function delete(int \$id): bool
    {
        \$sql = "DELETE FROM {\$this->table} WHERE id = :id";
        \$stmt = \$this->db->prepare(\$sql);

        return \$stmt->execute(['id' => \$id]);
    }
}
PHP;

    file_put_contents($modelPath, $template);
    Console::log("  - Model criado:      app/Models/{$modelName}.php");
}

function generateMigration(array $vars): void
{
    $tableName = $vars['tableName'];
    $migrationsDir = __DIR__ . '/migrations';
    if (!is_dir($migrationsDir)) {
        mkdir($migrationsDir, 0775, true);
    }
    
    $existingMigration = glob("{$migrationsDir}/*_create_{$tableName}_table.php");
    if (!empty($existingMigration)) {
        Console::warning("Aviso: Migração para criar a tabela '{$tableName}' já existe. Nenhuma ação foi tomada.");
        return;
    }

    $migrationName = "create_{$tableName}_table";
    $className = 'Create' . str_replace(' ', '', ucwords(str_replace('_', ' ', $tableName))) . 'Table';
    $timestamp = date('Y_m_d_His');
    $migrationFilename = "{$timestamp}_{$migrationName}.php";
    $migrationPath = "{$migrationsDir}/{$migrationFilename}";

    $upSql = "CREATE TABLE `{$tableName}` (
            id INT AUTO_INCREMENT PRIMARY KEY,
            -- TODO: Adicione as colunas da sua tabela aqui.
            -- nome VARCHAR(191) NOT NULL,
            created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
    $downSql = "DROP TABLE IF EXISTS `{$tableName}`";

    $template = getMigrationTemplate($className, $upSql, $downSql);
    file_put_contents($migrationPath, $template);
    Console::log("  - Migração criada:   database/migrations/{$migrationFilename}");
}

function generateAlterMigration(string $modelName, array $newColumns): void
{
    $tableName = strtolower($modelName) . 's';
    $newColumnNames = implode('_and_', array_keys($newColumns));
    $migrationName = "add_{$newColumnNames}_to_{$tableName}_table";
    $className = 'Add' . str_replace(' ', '', ucwords(str_replace('_', ' ', $newColumnNames))) . 'To' . ucfirst($tableName) . 'Table';
    $timestamp = date('Y_m_d_His');
    $migrationFilename = "{$timestamp}_{$migrationName}.php";
    $migrationsDir = __DIR__ . '/migrations';
    $migrationPath = "{$migrationsDir}/{$migrationFilename}";
    $upSqlLines = [];
    $downSqlLines = [];
    $previousColumn = 'id';
    foreach ($newColumns as $columnName => $columnDef) {
        $upSqlLines[] = "ADD COLUMN `{$columnName}` {$columnDef} AFTER `{$previousColumn}`";
        $downSqlLines[] = "DROP COLUMN `{$columnName}`";
        $previousColumn = $columnName;
    }
    $upSql = "ALTER TABLE `{$tableName}` " . implode(",\n              ", $upSqlLines);
    $downSql = "ALTER TABLE `{$tableName}` " . implode(",\n              ", $downSqlLines);
    $template = getMigrationTemplate($className, $upSql, $downSql);
    file_put_contents($migrationPath, $template);
    Console::log("  - Migração de alteração criada: database/migrations/{$migrationFilename}");
}

// PADRÃO FINAL: Gerador de Controller com sintaxe de view correta.
/**
 * Gera o arquivo do Controller com base no padrão EmpresaController.php.
 */
function generateController(array $vars): void
{
    $modelName = $vars['modelName'];
    $modelNameLower = $vars['modelNameLower'];
    $tableName = $vars['tableName'];
    $controllerName = "{$modelName}Controller";
    $controllersDir = dirname(__DIR__) . "/app/Controllers";
    if (!is_dir($controllersDir)) mkdir($controllersDir, 0775, true);
    $controllerPath = "{$controllersDir}/{$controllerName}.php";

    if (file_exists($controllerPath)) {
        Console::warning("Aviso: Controller 'app/Controllers/{$controllerName}.php' já existe.");
        return;
    }

    $template = <<<PHP
<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Validator;
use App\Models\\{$modelName};

/**
 * Controlador para gerenciar as operações CRUD de {$modelName}s.
 */
class {$controllerName} extends Controller
{
    private {$modelName} \${$modelNameLower}Model;

    /**
     * Construtor do controller.
     *
     * Inicializa o model de {$modelName} e chama o construtor pai.
     */
    public function __construct()
    {
        parent::__construct();
        \$this->{$modelNameLower}Model = new {$modelName}();
    }

    /**
     * Exibe a lista de todos os recursos.
     */
    public function index(): void
    {
        \${$tableName} = \$this->{$modelNameLower}Model->all();
        \$this->view('{$modelNameLower}.index', ['{$tableName}' => \${$tableName}]);
    }

    /**
     * Exibe o formulário para criar um novo recurso.
     */
    public function create(): void
    {
        \$this->view('{$modelNameLower}.create');
    }

    /**
     * Salva um novo recurso no banco de dados.
     */
    public function store(): void
    {
        \$data = Request::all();

        // TODO: Defina suas regras de validação para {$modelName}
        \$validator = new Validator();
        \$validator->validate(\$data, [
            // 'campo' => 'required|max:191',
        ]);

        if (\$validator->fails()) {
            \$this->view('{$modelNameLower}.create', [
                'errors' => \$validator->getErrors(),
                'old' => \$data
            ]);
            return;
        }

        \$this->{$modelNameLower}Model->create(\$data);
        \$this->redirect('/{$tableName}');
    }

    /**
     * Exibe o formulário para editar um recurso existente.
     */
    public function edit(int \$id): void
    {
        \${$modelNameLower} = \$this->{$modelNameLower}Model->find(\$id);

        if (!\${$modelNameLower}) {
            http_response_code(404);
            \$this->view('errors.404'); // CORREÇÃO: Usa ponto na view de erro.
            return;
        }

        \$this->view('{$modelNameLower}.edit', ['{$modelNameLower}' => \${$modelNameLower}]);
    }

    /**
     * Atualiza um recurso existente no banco de dados.
     */
    public function update(int \$id): void
    {
        \${$modelNameLower} = \$this->{$modelNameLower}Model->find(\$id);

        if (!\${$modelNameLower}) {
            http_response_code(404);
            \$this->view('errors.404'); // CORREÇÃO: Usa ponto na view de erro.
            return;
        }

        \$data = Request::all();

        // TODO: Defina suas regras de validação para {$modelName}
        \$validator = new Validator();
        \$validator->validate(\$data, [
            // 'campo' => 'required|max:191',
        ]);

        if (\$validator->fails()) {
            \$this->view('{$modelNameLower}.edit', [
                '{$modelNameLower}' => \${$modelNameLower}, 
                'errors' => \$validator->getErrors(), 
                'old' => \$data
            ]);
            return;
        }

        \$this->{$modelNameLower}Model->update(\$id, \$data);
        \$this->redirect('/{$tableName}');
    }

    /**
     * Exclui um recurso do banco de dados.
     */
    public function destroy(int \$id): void
    {
        \$this->{$modelNameLower}Model->delete(\$id);
        \$this->redirect('/{$tableName}');
    }
}
PHP;

    file_put_contents($controllerPath, $template);
    Console::log("  - Controller criado: app/Controllers/{$controllerName}.php");
}

function runMigrations(): void
{
    Console::header("GESTORSYSTEM - MIGRATION RUNNER");
    $pdo = getDbConnection();
    ensureMigrationsTableExists($pdo);
    $ranMigrations = $pdo->query("SELECT migration FROM migrations")->fetchAll(PDO::FETCH_COLUMN);
    $allFiles = glob(__DIR__ . '/migrations/*.php');
    sort($allFiles);
    $pendingMigrations = array_filter($allFiles, fn($file) => !in_array(basename($file), $ranMigrations));
    if (empty($pendingMigrations)) {
        Console::success("Banco de dados já está atualizado. Nenhuma migração para executar.");
        exit;
    }
    $lastBatch = (int)$pdo->query("SELECT MAX(batch) FROM migrations")->fetchColumn();
    $batch = $lastBatch + 1;
    Console::log("Iniciando migrações (lote: {$batch})...");
    foreach ($pendingMigrations as $file) {
        $baseMigrationPath = __DIR__ . "/Migration.php";
        if (file_exists($baseMigrationPath)) {
            require_once $baseMigrationPath;
        }
        require_once $file;
        $className = getClassNameFromFile(basename($file));
        if (!class_exists($className)) {
            Console::error("ERRO: Classe de migração '{$className}' não encontrada no arquivo '" . basename($file) . "'.");
            exit(1);
        }
        try {
            $migration = new $className($pdo);
            $migration->up();
            $stmt = $pdo->prepare("INSERT INTO migrations (migration, batch) VALUES (?, ?)");
            $stmt->execute([basename($file), $batch]);
            Console::log("  - Migrado: " . basename($file));
        } catch (Exception $e) {
            Console::error("\nERRO DURANTE A MIGRAÇÃO: " . $e->getMessage());
            exit(1);
        }
    }
    Console::success("\nMigrações concluídas com sucesso.");
}

function handleRollbackCommand(): void
{
    Console::header("GESTORSYSTEM - MIGRATION ROLLBACK");
    $pdo = getDbConnection();
    if (!tableExists($pdo, 'migrations')) {
        Console::warning("Nenhuma migração encontrada para reverter.");
        exit;
    }
    $lastBatch = $pdo->query("SELECT MAX(batch) FROM migrations")->fetchColumn();
    if (!$lastBatch) {
        Console::warning("Nada para reverter.");
        exit;
    }
    Console::log("Revertendo o último lote (batch: {$lastBatch})...");
    $stmt = $pdo->prepare("SELECT migration FROM migrations WHERE batch = ? ORDER BY migration DESC");
    $stmt->execute([$lastBatch]);
    $migrationsToRollback = $stmt->fetchAll(PDO::FETCH_COLUMN);
    if (empty($migrationsToRollback)) {
        Console::warning("Nenhuma migração encontrada no último lote.");
        exit;
    }
    foreach ($migrationsToRollback as $migrationFile) {
        $file = __DIR__ . '/migrations/' . $migrationFile;
        if (!file_exists($file)) {
            Console::warning("  - AVISO: Arquivo de migração '{$migrationFile}' não encontrado. Pulando.");
            continue;
        }
        $baseMigrationPath = __DIR__ . "/Migration.php";
        if (file_exists($baseMigrationPath)) {
            require_once $baseMigrationPath;
        }
        require_once $file;
        $className = getClassNameFromFile($migrationFile);
        if (!class_exists($className)) {
            Console::warning("  - AVISO: Classe '{$className}' não encontrada no arquivo '{$migrationFile}'. Pulando.");
            continue;
        }
        try {
            $migration = new $className($pdo);
            $migration->down();
            $deleteStmt = $pdo->prepare("DELETE FROM migrations WHERE migration = ?");
            $deleteStmt->execute([$migrationFile]);
            Console::log("  - Revertido: " . $migrationFile);
        } catch (Exception $e) {
            Console::error("\nERRO DURANTE O ROLLBACK: " . $e->getMessage());
            exit(1);
        }
    }
    Console::success("\nRollback concluído com sucesso.");
}


// ===================================================================
// FUNÇÕES AUXILIARES E DE BANCO DE DADOS
// ===================================================================

function getOrderedPublicPropertyNamesFromFile(string $modelFilePath): array
{
    if (!file_exists($modelFilePath)) return [];
    $fileContent = file_get_contents($modelFilePath);
    preg_match_all('/public\s+[^$]+\$(\w+)/', $fileContent, $matches);
    return $matches[1] ?? [];
}

function getDbConnection(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        require_once dirname(__DIR__) . '/vendor/autoload.php';
        $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
        $dotenv->load();
        try {
            $config = require dirname(__DIR__) . '/config/database.php';
            $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $pdo = new PDO($dsn, $config['user'], $config['pass'], $options);
        } catch (PDOException $e) {
            Console::error("ERRO FATAL: Não foi possível conectar ao banco de dados. Verifique seu .env e as configurações em config/database.php");
            Console::error("Detalhes: " . $e->getMessage());
            exit(1);
        }
    }
    return $pdo;
}

function mapPropertyToSql(ReflectionProperty $property): string
{
    $docComment = $property->getDocComment();
    $type = $property->getType();
    $typeName = $type ? $type->getName() : 'string';
    $isNullable = $type ? $type->allowsNull() : true;
    $sqlType = '';
    if ($docComment && preg_match('/@var\s+([a-zA-Z0-9_\\\\()]+)/', $docComment, $matches)) {
        $docType = strtolower($matches[1]);
        if (str_starts_with($docType, 'decimal')) $sqlType = strtoupper($docType);
        elseif (str_starts_with($docType, 'varchar')) $sqlType = strtoupper($docType);
        elseif (str_starts_with($docType, 'char')) $sqlType = strtoupper($docType);
        elseif ($docType === 'text') $sqlType = 'TEXT';
        elseif (str_starts_with($docType, 'int')) $sqlType = strtoupper($docType);
    }
    if (empty($sqlType)) {
        $sqlType = match ($typeName) {
            'int' => 'INT(11)', 'bool' => 'TINYINT(1)',
            'float' => 'DECIMAL(10, 2)',
            \DateTime::class, 'datetime' => 'DATETIME',
            default => 'VARCHAR(255)',
        };
    }
    $sqlNull = $isNullable ? 'NULL DEFAULT NULL' : 'NOT NULL';
    return "{$sqlType} {$sqlNull}";
}

function tableExists(PDO $pdo, string $tableName): bool
{
    try {
        $pdo->query("SELECT 1 FROM `{$tableName}` LIMIT 1");
        return true;
    } catch (Exception $e) {
        return false;
    }
}

function getTableColumns(PDO $pdo, string $tableName): array
{
    $stmt = $pdo->query("DESCRIBE `{$tableName}`");
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function ensureMigrationsTableExists(PDO $pdo): void
{
    $pdo->exec("CREATE TABLE IF NOT EXISTS `migrations` (
          `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
          `migration` VARCHAR(255) NOT NULL UNIQUE,
          `batch` INT NOT NULL
        ) ENGINE=InnoDB;");
}

function getMigrationTemplate(string $className, string $upSql, string $downSql): string
{
    return <<<PHP
<?php

declare(strict_types=1);

use Database\Migration;

/**
 * Migração para a tabela gerada.
 */
class {$className} extends Migration
{
    /**
     * Executa a migração (cria a tabela/colunas).
     */
    public function up(): void
    {
        \$this->pdo->exec("{$upSql}");
    }

    /**
     * Reverte a migração (remove a tabela/colunas).
     */
    public function down(): void
    {
        \$this->pdo->exec("{$downSql}");
    }
}
PHP;
}

function getClassNameFromFile(string $filename): string
{
    $baseName = preg_replace(['/^\d{4}_\d{2}_\d{2}_\d{6}_/', '/\.php$/'], '', $filename);
    $className = str_replace(' ', '', ucwords(str_replace('_', ' ', $baseName)));
    return $className;
}