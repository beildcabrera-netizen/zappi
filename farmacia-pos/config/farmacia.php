<?php

return [
    'iva_porcentaje' => env('FARMACIA_IVA', 13),
    'moneda_simbolo' => env('FARMACIA_MONEDA', 'Bs'),
    'llave_dosificacion' => env('FARMACIA_LLAVE_DOSIFICACION', ''),
    'tiempo_expiracion_minutos' => env('FARMACIA_EXPIRACION_VENTA', 15),
];
