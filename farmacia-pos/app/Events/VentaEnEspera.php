<?php

namespace App\Events;

use App\Models\Sale;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class VentaEnEspera implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public Sale $venta,
        public int $cajaId
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('caja.' . $this->cajaId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'venta.en.espera';
    }
}
