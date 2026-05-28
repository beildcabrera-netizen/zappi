<?php

namespace App\Exceptions;

use Exception;

class TalonarioExpiradoException extends Exception
{
    public function __construct(string $talonario = '')
    {
        $message = "El talonario '{$talonario}' ha expirado.";
        parent::__construct($message, 422);
    }
}
