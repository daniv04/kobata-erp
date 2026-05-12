<?php

namespace Tests\Feature;

use App\Services\Facturacion\Builders\DetalleBuilder;
use Tests\TestCase;

class DetalleBuilderExoneracionTest extends TestCase
{
    private DetalleBuilder $builder;

    private array $baseItem;

    private array $exoneracion;

    protected function setUp(): void
    {
        parent::setUp();

        $this->builder = new DetalleBuilder;

        $this->baseItem = [
            'product_id' => 1,
            'name' => 'Servicio de prueba',
            'cabys_code' => '5111501010100',
            'quantity' => 1,
            'unit_price' => 10000,
            'tax_percentage' => 13,
            'discount_enabled' => false,
            'discount_percentage' => 0,
            'discount_type' => '07',
            'exoneracion' => null,
        ];

        $this->exoneracion = [
            'tipo_documento' => '08',
            'numero_documento' => 'ZF-2024-001',
            'nombre_institucion' => '01',
            'fecha_emision' => '2024-01-15T00:00:00+06:00',
            'tarifa_exonerada' => 13,
            'tipo_documento_otro' => null,
            'articulo' => null,
            'inciso' => null,
            'nombre_institucion_otros' => null,
        ];
    }

    public function test_line_without_exoneracion_calculates_normally(): void
    {
        $result = $this->builder->build([$this->baseItem]);
        $line = $result['DetalleServicio']['LineaDetalle'][0];

        $this->assertEquals(10000, $line['MontoTotal']);
        $this->assertEquals(10000, $line['SubTotal']);
        $this->assertEquals(1300, $line['Impuesto'][0]['Monto']);
        $this->assertEquals(0, $line['ImpuestoNeto'] - 1300); // ImpuestoNeto = 1300
        $this->assertEquals(11300, $line['MontoTotalLinea']);
        $this->assertArrayNotHasKey('Exoneracion', $line['Impuesto'][0]);
    }

    public function test_line_with_full_exoneracion_adds_exoneracion_node(): void
    {
        $item = array_merge($this->baseItem, ['exoneracion' => $this->exoneracion]);

        $result = $this->builder->build([$item]);
        $line = $result['DetalleServicio']['LineaDetalle'][0];

        $this->assertArrayHasKey('Exoneracion', $line['Impuesto'][0]);

        $ex = $line['Impuesto'][0]['Exoneracion'];
        $this->assertEquals('08', $ex['TipoDocumentoEX1']);
        $this->assertEquals('ZF-2024-001', $ex['NumeroDocumento']);
        $this->assertEquals('01', $ex['NombreInstitucion']);
        $this->assertEquals(13, $ex['TarifaExonerada']);
        $this->assertEquals(1300, $ex['MontoExoneracion']); // 13% * 10000
    }

    public function test_impuesto_neto_is_zero_on_full_exoneracion(): void
    {
        $item = array_merge($this->baseItem, ['exoneracion' => $this->exoneracion]);

        $result = $this->builder->build([$item]);
        $line = $result['DetalleServicio']['LineaDetalle'][0];

        $this->assertEquals(0, $line['ImpuestoNeto']);
        $this->assertEquals(10000, $line['MontoTotalLinea']); // subtotal + 0 impuesto neto
    }

    public function test_partial_exoneracion_reduces_impuesto_neto(): void
    {
        $exParcial = array_merge($this->exoneracion, ['tarifa_exonerada' => 6.5]);
        $item = array_merge($this->baseItem, ['exoneracion' => $exParcial]);

        $result = $this->builder->build([$item]);
        $line = $result['DetalleServicio']['LineaDetalle'][0];

        $impuesto = $line['Impuesto'][0];
        $this->assertEquals(1300, $impuesto['Monto']); // IVA total sigue siendo 13%
        $this->assertEquals(650, $impuesto['Exoneracion']['MontoExoneracion']); // 6.5% * 10000
        $this->assertEquals(650, $line['ImpuestoNeto']); // 1300 - 650
        $this->assertEquals(10650, $line['MontoTotalLinea']); // 10000 + 650
    }

    public function test_exoneracion_is_not_applied_to_zero_tax_lines(): void
    {
        $item = array_merge($this->baseItem, [
            'tax_percentage' => 0,
            'exoneracion' => $this->exoneracion,
        ]);

        $result = $this->builder->build([$item]);
        $line = $result['DetalleServicio']['LineaDetalle'][0];

        $this->assertArrayNotHasKey('Exoneracion', $line['Impuesto'][0]);
        $this->assertEquals(0, $line['ImpuestoNeto']);
    }
}
