<?php

namespace App\Exceptions;

use Exception;

class ProductImportException extends Exception
{
    protected $duplicates = [];
    protected $inserted = 0;

    /**
     * Constructor para la excepción.
     *
     * @param string $message Mensaje de error
     * @param array $duplicates Lista de SKUs duplicados
     * @param int $inserted Cantidad de registros insertados correctamente
     * @param int $code Código de error
     * @param \Throwable|null $previous Excepción previa
     */
    public function __construct(
        string $message,
        array $duplicates = [],
        int $inserted = 0,
        int $code = 0,
        \Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->duplicates = $duplicates;
        $this->inserted = $inserted;
    }

    /**
     * Obtiene la lista de SKUs duplicados.
     *
     * @return array
     */
    public function getDuplicates(): array
    {
        return $this->duplicates;
    }

    /**
     * Obtiene la cantidad de registros insertados correctamente.
     *
     * @return int
     */
    public function getInsertedCount(): int
    {
        return $this->inserted;
    }
} 