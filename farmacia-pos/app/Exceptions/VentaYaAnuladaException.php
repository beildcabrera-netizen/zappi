<?php

namespace App\Exceptions;

use Exception;

class VentaYaAnuladaException extends Exception
{
    public function __construct(int $ventaId = 0)
    {
        $message = "La venta #{$ventaId} ya fue anulada anteriormente.";
        parent::__construct($message, 422);
    }
}
