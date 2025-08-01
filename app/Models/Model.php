<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

/**
 * Classe Model base abstrata.
 *
 * Esta classe serve como a fundação para todos os outros models da aplicação.
 * Ela lida com a conexão do banco de dados, disponibilizando-a para todas as
 * classes filhas. É declarada como 'abstract' pois não deve ser instanciada diretamente.
 */
abstract class Model
{
    /**
     * A instância da conexão com o banco de dados.
     *
     * Esta propriedade é protegida para que possa ser acessada pelas classes filhas (models).
     * Ela armazena o objeto PDO retornado pelo singleton Database.
     *
     * @var PDO
     */
    protected PDO $db;

    /**
     * Os atributos que podem ser atribuídos em massa.
     * A ser definido nas classes filhas.
     * @var array
     */
    protected array $fillable = [];

    /**
     * Construtor do Model.
     *
     * Quando um novo model é instanciado, este construtor automaticamente
     * recupera a instância da conexão com o banco de dados e a atribui
     * à propriedade $db.
     */
    public function __construct()
    {
        // Obtém a instância singleton da conexão com o banco de dados.
        $this->db = Database::getInstance();
    }

    /**
     * Filtra os dados de entrada para permitir apenas os campos definidos na propriedade $fillable.
     * Isso protege contra a atribuição em massa de campos não desejados.
     *
     * @param array $data Os dados brutos de entrada.
     * @return array Os dados filtrados.
     */
    protected function filterFillable(array $data): array
    {
        if (empty($this->fillable)) {
            return $data;
        }

        return array_intersect_key($data, array_flip($this->fillable));
    }
}