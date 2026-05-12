<?php

namespace Tests\Unit;

use App\Services\Facturacion\Builders\ResumenBuilder;
use PHPUnit\Framework\TestCase;

class ResumenBuilderTest extends TestCase
{
    private ResumenBuilder $builder;

    /** @var array<int, array<string, mixed>> */
    private array $items;

    protected function setUp(): void
    {
        parent::setUp();

        $this->builder = new ResumenBuilder;

        $this->items = [
            [
                'quantity' => 2,
                'unit_price' => 1000,
                'tax_percentage' => 13,
                'discount_enabled' => false,
                'discount_percentage' => null,
            ],
        ];
    }

    public function test_moneda_crc_con_tipo_cambio_uno(): void
    {
        $result = $this->builder->build($this->items, 'CRC', [
            ['type' => '01', 'amount' => 2260, 'others_description' => null],
        ]);

        $moneda = $result['ResumenFactura']['CodigoTipoMoneda'];
        $this->assertSame('CRC', $moneda['CodigoMoneda']);
        $this->assertSame(1.0, $moneda['TipoCambio']);
    }

    public function test_moneda_usd_con_tipo_cambio_quinientos(): void
    {
        $result = $this->builder->build($this->items, 'USD', [
            ['type' => '01', 'amount' => 2260, 'others_description' => null],
        ]);

        $moneda = $result['ResumenFactura']['CodigoTipoMoneda'];
        $this->assertSame('USD', $moneda['CodigoMoneda']);
        $this->assertSame(500.0, $moneda['TipoCambio']);
    }

    public function test_moneda_eur_con_tipo_cambio_seiscientos(): void
    {
        $result = $this->builder->build($this->items, 'EUR', [
            ['type' => '01', 'amount' => 2260, 'others_description' => null],
        ]);

        $moneda = $result['ResumenFactura']['CodigoTipoMoneda'];
        $this->assertSame('EUR', $moneda['CodigoMoneda']);
        $this->assertSame(600.0, $moneda['TipoCambio']);
    }

    public function test_un_metodo_de_pago(): void
    {
        $result = $this->builder->build($this->items, 'CRC', [
            ['type' => '01', 'amount' => 2260, 'others_description' => null],
        ]);

        $medioPago = $result['ResumenFactura']['MedioPago'];
        $this->assertCount(1, $medioPago);
        $this->assertSame('01', $medioPago[0]['TipoMedioPago']);
        $this->assertSame(2260.0, $medioPago[0]['TotalMedioPago']);
        $this->assertArrayNotHasKey('MedioPagoOtros', $medioPago[0]);
    }

    public function test_multiples_metodos_de_pago(): void
    {
        $result = $this->builder->build($this->items, 'CRC', [
            ['type' => '01', 'amount' => 1000, 'others_description' => null],
            ['type' => '02', 'amount' => 1260, 'others_description' => null],
        ]);

        $medioPago = $result['ResumenFactura']['MedioPago'];
        $this->assertCount(2, $medioPago);
        $this->assertSame('01', $medioPago[0]['TipoMedioPago']);
        $this->assertSame(1000.0, $medioPago[0]['TotalMedioPago']);
        $this->assertSame('02', $medioPago[1]['TipoMedioPago']);
        $this->assertSame(1260.0, $medioPago[1]['TotalMedioPago']);
    }

    public function test_metodo_otros_incluye_descripcion(): void
    {
        $result = $this->builder->build($this->items, 'CRC', [
            ['type' => '99', 'amount' => 2260, 'others_description' => 'Criptomoneda'],
        ]);

        $medioPago = $result['ResumenFactura']['MedioPago'];
        $this->assertSame('99', $medioPago[0]['TipoMedioPago']);
        $this->assertSame('Criptomoneda', $medioPago[0]['MedioPagoOtros']);
    }

    public function test_totales_sin_descuento(): void
    {
        // 2 unidades × ₡1000 bruto=2000, sin descuento, IVA 13%=260 → total 2260
        $result = $this->builder->build($this->items, 'CRC', [
            ['type' => '01', 'amount' => 2260, 'others_description' => null],
        ]);

        $resumen = $result['ResumenFactura'];
        $this->assertSame(2000.0, $resumen['TotalMercanciasGravadas']);
        $this->assertSame(0.0, $resumen['TotalMercanciasExentas']);
        $this->assertSame(2000.0, $resumen['TotalVenta']);
        $this->assertSame(0.0, $resumen['TotalDescuentos']);
        $this->assertSame(2000.0, $resumen['TotalVentaNeta']);
        $this->assertSame(260.0, $resumen['TotalImpuesto']);
        $this->assertSame(2260.0, $resumen['TotalComprobante']);
    }

    public function test_descuento_afecta_totales(): void
    {
        $items = [
            [
                'quantity' => 1,
                'unit_price' => 1000,
                'tax_percentage' => 13,
                'discount_enabled' => true,
                'discount_percentage' => 10,
            ],
        ];

        // bruto=1000, descuento=100, neto=900, IVA(13% sobre 900)=117, total=1017
        $result = $this->builder->build($items, 'CRC', [
            ['type' => '01', 'amount' => 1017, 'others_description' => null],
        ]);

        $resumen = $result['ResumenFactura'];
        $this->assertSame(1000.0, $resumen['TotalMercanciasGravadas']); // bruto
        $this->assertSame(1000.0, $resumen['TotalVenta']);               // bruto
        $this->assertSame(100.0, $resumen['TotalDescuentos']);
        $this->assertSame(900.0, $resumen['TotalVentaNeta']);            // neto
        $this->assertSame(117.0, $resumen['TotalImpuesto']);
        $this->assertSame(1017.0, $resumen['TotalComprobante']);
    }
}
