<?php

namespace App\Exceptions;

use Exception;

class ProductoNoEncontradoException extends Exception
{
    public function __construct(string $codigo = '')
    {
        $message = "Producto no encontrado: '{$codigo}'.";
        parent::__construct($message, 404);
    }
}
