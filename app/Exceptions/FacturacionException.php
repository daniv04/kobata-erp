<?php

namespace App\Exceptions;

use RuntimeException;

class FacturacionException extends RuntimeException
{
    /** @var array<string, string[]> */
    private array $validationErrors;

    /** @param array<string, string[]> $errors */
    private function __construct(string $message, array $errors = [], ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
        $this->validationErrors = $errors;
    }

    /** @return array<string, string[]> */
    public function getValidationErrors(): array
    {
        return $this->validationErrors;
    }

    public function hasValidationErrors(): bool
    {
        return ! empty($this->validationErrors);
    }

    // ── Named constructors ────────────────────────────────────────────────────

    /** @param array<string, string[]> $errors */
    public static function validacionFallida(array $errors): self
    {
        return new self('La factura contiene errores de validación.', $errors);
    }

    public static function haciendaNoDisponible(?\Throwable $previous = null): self
    {
        return new self('No fue posible conectar con Hacienda. Intente de nuevo más tarde.', [], $previous);
    }

    public static function payloadInvalido(string $razon): self
    {
        return new self("El payload de la factura es inválido: {$razon}");
    }

    public static function clienteNoEncontrado(int $clientId): self
    {
        return new self("El cliente con ID {$clientId} no existe.");
    }
}
