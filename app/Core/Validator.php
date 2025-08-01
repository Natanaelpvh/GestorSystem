<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Classe Validator para validar dados de entrada.
 *
 * Fornece uma maneira simples e fluente de validar um array de dados
 * contra um conjunto de regras predefinidas.
 */
class Validator
{
    /**
     * Armazena as mensagens de erro de validação.
     *
     * @var array
     */
    private array $errors = [];

    /**
     * Valida um conjunto de dados com base em regras fornecidas.
     *
     * @param array $data Os dados a serem validados (ex: $_POST).
     * @param array $rules As regras de validação para cada campo.
     *                     Ex: ['name' => 'required|min:3', 'email' => 'required|email']
     * @return self Retorna a própria instância para permitir method chaining.
     */
    public function validate(array $data, array $rules): self
    {
        $this->errors = [];

        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? null;
            // Divide as regras (ex: 'required|min:3') em um array.
            $rulesArray = explode('|', $fieldRules);

            foreach ($rulesArray as $rule) {
                $this->applyRule($field, $value, $rule);
            }
        }

        return $this;
    }

    /**
     * Aplica uma regra de validação específica a um campo.
     *
     * @param string $field O nome do campo.
     * @param mixed $value O valor do campo.
     * @param string $rule A regra a ser aplicada (ex: 'required', 'min:5').
     * @return void
     */
    private function applyRule(string $field, mixed $value, string $rule): void
    {
        $param = null;
        // Verifica se a regra tem um parâmetro (ex: min:3)
        if (str_contains($rule, ':')) {
            [$rule, $param] = explode(':', $rule, 2);
        }

        switch ($rule) {
            case 'required':
                if (empty($value)) {
                    $this->addError($field, "O campo {$field} é obrigatório.");
                }
                break;

            case 'email':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, "O campo {$field} deve ser um endereço de e-mail válido.");
                }
                break;

            case 'min':
                if (!empty($value) && mb_strlen((string)$value) < (int)$param) {
                    $this->addError($field, "O campo {$field} deve ter no mínimo {$param} caracteres.");
                }
                break;

            case 'max':
                if (!empty($value) && mb_strlen((string)$value) > (int)$param) {
                    $this->addError($field, "O campo {$field} não pode ter mais de {$param} caracteres.");
                }
                break;
        }
    }

    /**
     * Adiciona uma mensagem de erro para um campo específico.
     *
     * @param string $field O campo que falhou na validação.
     * @param string $message A mensagem de erro.
     * @return void
     */
    private function addError(string $field, string $message): void
    {
        // Garante que o array de erros para este campo seja inicializado.
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }

    /**
     * Verifica se a validação falhou.
     *
     * @return bool Retorna true se houver erros, false caso contrário.
     */
    public function fails(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Obtém todas as mensagens de erro.
     *
     * @return array Um array associativo com os campos e suas respectivas mensagens de erro.
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}