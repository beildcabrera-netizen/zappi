<?php

namespace App\Exceptions;

use Exception;

class SinTurnoActivoException extends Exception
{
    public function __construct()
    {
        parent::__construct('No hay un turno activo. Debe abrir turno primero.', 403);
    }
}
