<?php

namespace App\Exceptions;

use Exception;

class InventoryException extends Exception
{
    /**
     * Create a new inventory exception instance.
     *
     * @param string $message
     * @param int $code
     * @param Exception|null $previous
     */
    public function __construct(string $message = "Error de inventario", int $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Create exception from SQL error state 45000
     *
     * @param string $sqlMessage
     * @return static
     */
    public static function fromSqlError(string $sqlMessage): static
    {
        return new static("Error de inventario: {$sqlMessage}", 45000);
    }

    /**
     * Create exception for insufficient stock
     *
     * @param string $productName
     * @param float $required
     * @param float $available
     * @return static
     */
    public static function insufficientStock(string $productName, float $required, float $available): static
    {
        return new static(
            "Stock insuficiente para el producto '{$productName}'. Requerido: {$required}, Disponible: {$available}",
            45001
        );
    }

    /**
     * Create exception for invalid movement type
     *
     * @param string $type
     * @return static
     */
    public static function invalidMovementType(string $type): static
    {
        return new static("Tipo de movimiento inválido: '{$type}'", 45002);
    }

    /**
     * Create exception for missing warehouse
     *
     * @param string $context
     * @return static
     */
    public static function missingWarehouse(string $context): static
    {
        return new static("Bodega requerida para {$context}", 45003);
    }

    /**
     * Create exception for validation errors
     *
     * @param array $errors
     * @return static
     */
    public static function validationFailed(array $errors): static
    {
        $message = "Errores de validación: " . implode(', ', $errors);
        return new static($message, 45004);
    }
}
