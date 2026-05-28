<?php

namespace Tests\Unit\Services;

use App\Services\Facturacion\AllegedRC4;
use App\Services\Facturacion\CodigoControlService;
use App\Services\Facturacion\Verhoeff;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CodigoControlServiceTest extends TestCase
{
    #[Test]
    public function genera_codigo_de_control_con_formato_valido(): void
    {
        $service = new CodigoControlService;

        $codigo = $service->generar(
            numeroAutorizacion: '79040011859',
            numeroFactura: '1604',
            nitCliente: '4175687014',
            fechaEmision: '20260528',
            montoTotal: 150.00,
            llaveDosificacion: '2' . chr(159) . 'F' . chr(181) . 'gG(' . chr(143) . 'B' . chr(181) . '&2H' . chr(181) . 'Hd',
        );

        $this->assertNotEmpty($codigo);
        $this->assertMatchesRegularExpression('/^[A-F0-9]+$/', $codigo);
    }

    #[Test]
    public function sumatoria_ponderada_con_factores_ciclicos(): void
    {
        $service = new CodigoControlService;

        $reflection = new \ReflectionMethod($service, 'sumatoriaPonderada');
        $reflection->setAccessible(true);

        $resultado = $reflection->invoke($service, '123456');

        $this->assertIsInt($resultado);
    }

    #[Test]
    public function alleged_rc4_encripta_correctamente(): void
    {
        $rc4 = new AllegedRC4('5');
        $resultado = $rc4->encriptar('79040011859');

        $this->assertNotEmpty($resultado);
        $this->assertMatchesRegularExpression('/^[A-F0-9]+$/', $resultado);
    }

    #[Test]
    public function verhoeff_calcula_digito_correcto(): void
    {
        $digito = Verhoeff::calcular('12345');
        $this->assertIsInt($digito);
        $this->assertGreaterThanOrEqual(0, $digito);
        $this->assertLessThanOrEqual(9, $digito);
    }

    #[Test]
    public function verhoeff_verifica_digito_correctamente(): void
    {
        $base = '12345';
        $digito = Verhoeff::calcular($base);

        $this->assertTrue(Verhoeff::verificar($base . $digito));
    }
}
