<?php

namespace Tests\Feature;

use App\Services\Facturacion\Builders\ResumenBuilder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ResumenBuilderExoneracionTest extends TestCase
{
    use LazilyRefreshDatabase;

    private ResumenBuilder $builder;

    private array $paymentMethods;

    private array $exoneracion;

    protected function setUp(): void
    {
        parent::setUp();

        $this->builder = new ResumenBuilder;
        $this->paymentMethods = [['type' => '01', 'amount' => 0]];
        $this->exoneracion = [
            'tipo_documento' => '08',
            'numero_documento' => 'ZF-2024-001',
            'nombre_institucion' => '01',
            'fecha_emision' => '2024-01-15T00:00:00+06:00',
            'tarifa_exonerada' => 13,
        ];
    }

    private function makeItem(array $overrides = []): array
    {
        return array_merge([
            'product_id' => 1,
            'cabys_code' => '5111501010100', // servicio (empieza en 5)
            'quantity' => 1,
            'unit_price' => 10000,
            'tax_percentage' => 13,
            'discount_enabled' => false,
            'discount_percentage' => 0,
            'exoneracion' => null,
        ], $overrides);
    }

    public function test_totals_without_exoneracion_remain_unchanged(): void
    {
        $result = $this->builder->build([$this->makeItem()], 'CRC', $this->paymentMethods);
        $resumen = $result['ResumenFactura'];

        $this->assertEquals(10000, $resumen['TotalServGravados']);
        $this->assertEquals(0, $resumen['TotalServExonerado']);
        $this->assertEquals(1300, $resumen['TotalImpuesto']);
        $this->assertEquals(11300, $resumen['TotalComprobante']);
        $this->assertEquals(0, $resumen['TotalExonerado']);
    }

    public function test_full_exoneracion_on_service_produces_zero_tax(): void
    {
        $item = $this->makeItem(['exoneracion' => $this->exoneracion]);
        $result = $this->builder->build([$item], 'CRC', $this->paymentMethods);
        $resumen = $result['ResumenFactura'];

        $this->assertEquals(0, $resumen['TotalImpuesto']);
        $this->assertGreaterThan(0, $resumen['TotalExonerado']);
        $this->assertEquals(10000, $resumen['TotalComprobante']);
        $this->assertEquals(0, $resumen['TotalMercExonerada']);
    }

    public function test_partial_exoneracion_reduces_tax(): void
    {
        $exParcial = array_merge($this->exoneracion, ['tarifa_exonerada' => 6.5]);
        $item = $this->makeItem(['exoneracion' => $exParcial]);
        $result = $this->builder->build([$item], 'CRC', $this->paymentMethods);
        $resumen = $result['ResumenFactura'];

        $this->assertEquals(650, $resumen['TotalImpuesto']);
        $this->assertEquals(10650, $resumen['TotalComprobante']);
    }

    public function test_mixed_items_one_with_one_without_exoneracion(): void
    {
        $items = [
            $this->makeItem(['exoneracion' => $this->exoneracion]),
            $this->makeItem(['exoneracion' => null]),
        ];
        $result = $this->builder->build($items, 'CRC', $this->paymentMethods);
        $resumen = $result['ResumenFactura'];

        $this->assertEquals(1300, $resumen['TotalImpuesto']);
        $this->assertEquals(21300, $resumen['TotalComprobante']);
    }

    public function test_exoneracion_on_merchandise_goes_to_merc_exonerada(): void
    {
        $item = $this->makeItem([
            'cabys_code' => '0111100010100', // mercancía (empieza en 0)
            'exoneracion' => $this->exoneracion,
        ]);
        $result = $this->builder->build([$item], 'CRC', $this->paymentMethods);
        $resumen = $result['ResumenFactura'];

        $this->assertGreaterThan(0, $resumen['TotalMercExonerada']);
        $this->assertEquals(0, $resumen['TotalServExonerado']);
        $this->assertEquals(0, $resumen['TotalImpuesto']);
    }
}
