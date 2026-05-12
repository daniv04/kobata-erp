<?php

namespace App\Services\Facturacion\Builders;

use App\Models\Products;
use Illuminate\Support\Collection;

class DetalleBuilder
{
    public function build(array $items): array
    {
        $cabysMap = Products::whereIn('id', array_column($items, 'product_id'))
            ->pluck('cabys_code', 'id');

        $lines = array_values(array_map(
            fn (array $item, int $index) => $this->buildLine($item, $index + 1, $cabysMap),
            $items,
            array_keys($items),
        ));

        return ['DetalleServicio' => ['LineaDetalle' => $lines]];
    }

    private function buildLine(array $item, int $lineNumber, Collection $cabysMap): array
    {
        $montoTotal = round($item['quantity'] * $item['unit_price'], 5);
        $descuento = $item['discount_enabled']
            ? round($montoTotal * (($item['discount_percentage'] ?? 0) / 100), 5)
            : 0;
        $subTotal = round($montoTotal - $descuento, 5);
        $taxRate = (float) ($item['tax_percentage'] ?? 13);
        $impuesto = round($subTotal * ($taxRate / 100), 5);
        $totalLinea = round($subTotal + $impuesto, 5);

        $line = [
            'NumeroLinea' => $lineNumber,
            'CodigoCABYS' => str_pad((string) ($cabysMap[$item['product_id']] ?? ''), 13, '0', STR_PAD_LEFT),
            'Cantidad' => $item['quantity'],
            'UnidadMedida' => 'Unid',
            'Detalle' => $item['name'],
            'PrecioUnitario' => $item['unit_price'],
            'MontoTotal' => $montoTotal,
            'SubTotal' => $subTotal,
            'BaseImponible' => $subTotal,
            'ImpuestoAsumidoEmisorFabrica' => 0,
            'ImpuestoNeto' => $impuesto,
            'MontoTotalLinea' => $totalLinea,
            'Impuesto' => [$this->buildImpuesto($taxRate, $subTotal)],
        ];

        if ($descuento > 0) {
            $line['Descuento'] = [[
                'MontoDescuento' => $descuento,
                'CodigoDescuento' => $item['discount_type'] ?? '07',
            ]];
        }

        return $line;
    }

    private function buildImpuesto(float $rate, float $base): array
    {
        return [
            'Codigo' => '01',
            'CodigoTarifaIVA' => $this->ivaCode($rate),
            'Tarifa' => $rate,
            'Monto' => round($base * ($rate / 100), 5),
        ];
    }

    private function ivaCode(float $rate): string
    {
        return match (true) {
            $rate == 0 => '01',
            $rate == 1 => '02',
            $rate == 2 => '03',
            $rate == 4 => '04',
            $rate == 8 => '07',
            $rate == 13 => '08',
            default => '08',
        };
    }
}
