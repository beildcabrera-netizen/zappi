<?php

namespace App\Exceptions;

use Exception;

class TalonarioAgotadoException extends Exception
{
    public function __construct(string $talonario = '')
    {
        $message = "El talonario '{$talonario}' ha agotado su rango de numeración.";
        parent::__construct($message, 422);
    }
}
