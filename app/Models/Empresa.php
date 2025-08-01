<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

/**
 * Model para a tabela 'empresas'.
 *
 * Esta classe representa uma empresa no banco de dados e fornece métodos
 * para interagir com a tabela 'empresas' (CRUD - Create, Read, Update, Delete).
 */
class Empresa extends Model
{
    /**
     * O nome da tabela do banco de dados associada a este model.
     *
     * @var string
     */
    protected string $table = 'empresas';

    /**
     * Os atributos que podem ser atribuídos em massa.
     * @var array
     */
    protected array $fillable = [
        'nome',
        'cnpj',
        'email',
        'telefone',
        'endereco',
    ];

    /** @var int|null O ID único da empresa. */
    public ?int $id = null;
    /** @var string|null O nome da empresa. */
    public ?string $nome = null;
    /** @var string|null O CNPJ da empresa. */
    public ?string $cnpj = null;
    /** @var string|null O e-mail de contato da empresa. */
    public ?string $email = null;
    /** @var string|null O telefone de contato da empresa. */
    public ?string $telefone = null;
    /** @var string|null O endereço da empresa. */
    public ?string $endereco = null;
    /** @var string|null O timestamp de quando a empresa foi criada. */
    public ?string $created_at = null;
    /** @var string|null O timestamp da última atualização da empresa. */
    public ?string $updated_at = null;

    /**
     * Busca uma empresa pelo seu ID.
     *
     * @param int $id O ID da empresa a ser encontrada.
     * @return array|null Retorna um array associativo com os dados da empresa ou null se não for encontrada.
     */
    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ?: null;
    }

    /**
     * Retorna todas as empresas do banco de dados.
     *
     * @return array Um array de arrays associativos, cada um representando uma empresa.
     */
    public function all(): array
    {
        $stmt = $this->db->query("SELECT * FROM {$this->table} ORDER BY nome ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Insere uma nova empresa no banco de dados.
     *
     * Define automaticamente os timestamps 'created_at' e 'updated_at'.
     *
     * @param array $data Um array associativo com os dados da empresa a ser criada.
     *                    As chaves devem corresponder aos nomes das colunas.
     * @return bool Retorna true em caso de sucesso, false em caso de falha.
     */
    public function create(array $data): bool
    {
        // Filtra os dados para permitir apenas os campos "fillable"
        $data = $this->filterFillable($data);

        // Define os timestamps se não forem fornecidos
        if (!isset($data['created_at'])) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }
        if (!isset($data['updated_at'])) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }

        // Monta a query de forma dinâmica e segura
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));

        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    /**
     * Atualiza os dados de uma empresa existente.
     *
     * Define automaticamente o timestamp 'updated_at'.
     *
     * @param int $id O ID da empresa a ser atualizada.
     * @param array $data Um array associativo com os dados a serem atualizados.
     * @return bool Retorna true em caso de sucesso, false em caso de falha.
     */
    public function update(int $id, array $data): bool
    {
        // Filtra os dados para permitir apenas os campos "fillable"
        $data = $this->filterFillable($data);

        // Define o timestamp de atualização
        if (!isset($data['updated_at'])) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }

        // Monta a cláusula SET dinamicamente
        $setClauses = [];
        foreach (array_keys($data) as $key) {
            $setClauses[] = "{$key} = :{$key}";
        }
        $setSql = implode(', ', $setClauses);

        $sql = "UPDATE {$this->table} SET {$setSql} WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        $data['id'] = $id;

        return $stmt->execute($data);
    }

    /**
     * Exclui uma empresa do banco de dados.
     *
     * @param int $id O ID da empresa a ser excluída.
     * @return bool Retorna true em caso de sucesso, false em caso de falha.
     */
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute(['id' => $id]);
    }
}