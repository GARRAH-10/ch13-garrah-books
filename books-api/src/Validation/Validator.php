<?php

declare(strict_types=1);

namespace App\Validation;

final class Validator
{
    private array $rules = [];
    private array $required = [];

    public function field(string $name, callable $rule, string $error): self
    {
        $this->rules[$name] = ['rule' => $rule, 'error' => $error];
        return $this;
    }

    public function required(string ...$names): self
    {
        $this->required = array_merge($this->required, $names);
        return $this;
    }

    public function validate(array $body, bool $partial = false): array
    {
        $errors = [];
        if (!$partial) {
            foreach ($this->required as $name) {
                if (!array_key_exists($name, $body)) {
                    $errors[$name] = "{$name} is required";
                }
            }
        }
        foreach ($this->rules as $name => $rule) {
            if (array_key_exists($name, $body) && !$rule['rule']($body[$name])) {
                $errors[$name] = $rule['error'];
            }
        }
        return $errors;
    }

    public static function nonEmptyString(int $max = 255): callable
    {
        return fn($value): bool =>
            is_string($value) && trim($value) !== '' && mb_strlen($value) <= $max;
    }

    public static function intRange(int $min, int $max): callable
    {
        return fn($value): bool =>
            is_numeric($value) && (int)$value >= $min && (int)$value <= $max;
    }

    public static function email(): callable
    {
        return fn($value): bool =>
            is_string($value) && filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }
}
