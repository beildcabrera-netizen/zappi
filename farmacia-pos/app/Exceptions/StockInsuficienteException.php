<?php

namespace App\Exceptions;

use Exception;

class StockInsuficienteException extends Exception
{
    public function __construct(string $producto = '', int $disponible = 0, int $requerido = 0)
    {
        $message = "Stock insuficiente para '{$producto}': disponible {$disponible}, requerido {$requerido}.";
        parent::__construct($message, 422);
    }
}
