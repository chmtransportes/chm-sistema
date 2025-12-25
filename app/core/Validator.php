<?php
/**
 * CHM Sistema - Validador de Dados
 * @author ch-mestriner (https://ch-mestriner.com.br)
 * @date 23/12/2025
 * @version 1.0.0
 */

namespace CHM\Core;

class Validator
{
    private array $data = [];
    private array $errors = [];
    private array $rules = [];

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    // Define dados para validação
    public function setData(array $data): self
    {
        $this->data = $data;
        return $this;
    }

    // Adiciona regra de validação
    public function rule(string $field, string $rules, ?string $label = null): self
    {
        $this->rules[$field] = [
            'rules' => $rules,
            'label' => $label ?? $field
        ];
        return $this;
    }

    // Valida todos os campos
    public function validate(): bool
    {
        $this->errors = [];

        foreach ($this->rules as $field => $config) {
            $rules = explode('|', $config['rules']);
            $value = $this->data[$field] ?? null;
            $label = $config['label'];

            foreach ($rules as $rule) {
                $params = [];
                if (strpos($rule, ':') !== false) {
                    [$rule, $paramStr] = explode(':', $rule, 2);
                    $params = explode(',', $paramStr);
                }

                $method = 'validate' . ucfirst($rule);
                if (method_exists($this, $method)) {
                    $result = $this->$method($value, $params, $field);
                    if ($result !== true) {
                        $this->addError($field, str_replace(':field', $label, $result));
                    }
                }
            }
        }

        return empty($this->errors);
    }

    // Adiciona erro
    public function addError(string $field, string $message): void
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }

    // Retorna erros
    public function getErrors(): array
    {
        return $this->errors;
    }

    // Retorna primeiro erro
    public function getFirstError(): ?string
    {
        foreach ($this->errors as $fieldErrors) {
            return $fieldErrors[0] ?? null;
        }
        return null;
    }

    // Verifica se tem erros
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    // Regra: obrigatório
    protected function validateRequired($value, $params, $field): bool|string
    {
        if ($value === null || $value === '' || (is_array($value) && empty($value))) {
            return ':field é obrigatório.';
        }
        return true;
    }

    // Regra: e-mail válido
    protected function validateEmail($value, $params, $field): bool|string
    {
        if (empty($value)) return true;
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return ':field deve ser um e-mail válido.';
        }
        return true;
    }

    // Regra: tamanho mínimo
    protected function validateMin($value, $params, $field): bool|string
    {
        if (empty($value)) return true;
        $min = (int)($params[0] ?? 0);
        if (mb_strlen($value) < $min) {
            return ":field deve ter no mínimo {$min} caracteres.";
        }
        return true;
    }

    // Regra: tamanho máximo
    protected function validateMax($value, $params, $field): bool|string
    {
        if (empty($value)) return true;
        $max = (int)($params[0] ?? 255);
        if (mb_strlen($value) > $max) {
            return ":field deve ter no máximo {$max} caracteres.";
        }
        return true;
    }

    // Regra: numérico
    protected function validateNumeric($value, $params, $field): bool|string
    {
        if (empty($value)) return true;
        if (!is_numeric($value)) {
            return ':field deve ser numérico.';
        }
        return true;
    }

    // Regra: inteiro
    protected function validateInteger($value, $params, $field): bool|string
    {
        if (empty($value)) return true;
        if (!filter_var($value, FILTER_VALIDATE_INT)) {
            return ':field deve ser um número inteiro.';
        }
        return true;
    }

    // Regra: CPF válido
    protected function validateCpf($value, $params, $field): bool|string
    {
        if (empty($value)) return true;
        if (!Helpers::validaCpf($value)) {
            return ':field deve ser um CPF válido.';
        }
        return true;
    }

    // Regra: CNPJ válido
    protected function validateCnpj($value, $params, $field): bool|string
    {
        if (empty($value)) return true;
        if (!Helpers::validaCnpj($value)) {
            return ':field deve ser um CNPJ válido.';
        }
        return true;
    }

    // Regra: confirmação (ex: password_confirmation)
    protected function validateConfirmed($value, $params, $field): bool|string
    {
        $confirmField = $field . '_confirmation';
        $confirmValue = $this->data[$confirmField] ?? null;
        if ($value !== $confirmValue) {
            return ':field não confere com a confirmação.';
        }
        return true;
    }

    // Regra: único no banco
    protected function validateUnique($value, $params, $field): bool|string
    {
        if (empty($value)) return true;
        $table = $params[0] ?? '';
        $column = $params[1] ?? $field;
        $exceptId = $params[2] ?? null;

        if (empty($table)) return true;

        $db = Database::getInstance();
        $sql = "SELECT COUNT(*) FROM " . DB_PREFIX . "{$table} WHERE {$column} = :value";
        $bindings = ['value' => $value];

        if ($exceptId) {
            $sql .= " AND id != :except_id";
            $bindings['except_id'] = $exceptId;
        }

        $count = $db->fetchColumn($sql, $bindings);
        if ($count > 0) {
            return ':field já está em uso.';
        }
        return true;
    }

    // Regra: existe no banco
    protected function validateExists($value, $params, $field): bool|string
    {
        if (empty($value)) return true;
        $table = $params[0] ?? '';
        $column = $params[1] ?? 'id';

        if (empty($table)) return true;

        $db = Database::getInstance();
        $sql = "SELECT COUNT(*) FROM " . DB_PREFIX . "{$table} WHERE {$column} = :value";
        $count = $db->fetchColumn($sql, ['value' => $value]);

        if ($count == 0) {
            return ':field não existe.';
        }
        return true;
    }

    // Regra: data válida
    protected function validateDate($value, $params, $field): bool|string
    {
        if (empty($value)) return true;
        $format = $params[0] ?? 'Y-m-d';
        $d = \DateTime::createFromFormat($format, $value);
        if (!$d || $d->format($format) !== $value) {
            return ':field deve ser uma data válida.';
        }
        return true;
    }

    // Regra: entre valores
    protected function validateBetween($value, $params, $field): bool|string
    {
        if (empty($value)) return true;
        $min = (float)($params[0] ?? 0);
        $max = (float)($params[1] ?? PHP_INT_MAX);
        $val = (float)$value;
        if ($val < $min || $val > $max) {
            return ":field deve estar entre {$min} e {$max}.";
        }
        return true;
    }

    // Regra: regex
    protected function validateRegex($value, $params, $field): bool|string
    {
        if (empty($value)) return true;
        $pattern = $params[0] ?? '';
        if (!preg_match($pattern, $value)) {
            return ':field tem formato inválido.';
        }
        return true;
    }

    // Regra: telefone
    protected function validatePhone($value, $params, $field): bool|string
    {
        if (empty($value)) return true;
        $phone = preg_replace('/\D/', '', $value);
        if (strlen($phone) < 10 || strlen($phone) > 11) {
            return ':field deve ser um telefone válido.';
        }
        return true;
    }

    // Regra: placa de veículo
    protected function validatePlate($value, $params, $field): bool|string
    {
        if (empty($value)) return true;
        $plate = preg_replace('/[^A-Za-z0-9]/', '', $value);
        if (!preg_match('/^[A-Z]{3}[0-9][A-Z0-9][0-9]{2}$/', strtoupper($plate))) {
            return ':field deve ser uma placa válida.';
        }
        return true;
    }
}
