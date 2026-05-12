<?php

namespace App\Services\Facturacion\Builders;

class DetalleBuilder
{
    public function build(array $items): array
    {
        $lines = array_values(array_map(
            fn (array $item, int $index) => $this->buildLine($item, $index + 1),
            $items,
            array_keys($items),
        ));

        return ['DetalleServicio' => ['LineaDetalle' => $lines]];
    }

    private function buildLine(array $item, int $lineNumber): array
    {
        $montoTotal = round($item['quantity'] * $item['unit_price'], 5);
        $descuento = $item['discount_enabled']
            ? round($montoTotal * (($item['discount_percentage'] ?? 0) / 100), 5)
            : 0;
        $subTotal = round($montoTotal - $descuento, 5);
        $taxRate = (float) ($item['tax_percentage'] ?? 13);
        $impuesto = round($subTotal * ($taxRate / 100), 5);

        $exoneracion = $item['exoneracion'] ?? null;
        $montoExoneracion = 0;

        if ($exoneracion && $taxRate > 0) {
            $montoExoneracion = round(((float) $exoneracion['tarifa_exonerada'] / 100) * $subTotal, 5);
        }

        $impuestoNeto = round($impuesto - $montoExoneracion, 5);
        $totalLinea = round($subTotal + $impuestoNeto, 5);

        $line = [
            'NumeroLinea' => $lineNumber,
            'CodigoCABYS' => str_pad((string) ($item['cabys_code'] ?? ''), 13, '0', STR_PAD_LEFT),
            'Cantidad' => $item['quantity'],
            'UnidadMedida' => 'Unid',
            'Detalle' => $item['name'],
            'PrecioUnitario' => $item['unit_price'],
            'MontoTotal' => $montoTotal,
        ];

        if ($descuento > 0) {
            $line['Descuento'] = [[
                'MontoDescuento' => $descuento,
                'CodigoDescuento' => $item['discount_type'] ?? '07',
            ]];
        }

        $line['SubTotal'] = $subTotal;
        $line['BaseImponible'] = $subTotal;
        $line['Impuesto'] = [$this->buildImpuesto($taxRate, $subTotal, $impuesto, $exoneracion, $montoExoneracion)];
        $line['ImpuestoAsumidoEmisorFabrica'] = 0;
        $line['ImpuestoNeto'] = $impuestoNeto;
        $line['MontoTotalLinea'] = $totalLinea;

        return $line;
    }

    private function buildImpuesto(float $rate, float $base, float $monto, ?array $exoneracion, float $montoExoneracion): array
    {
        $impuesto = [
            'Codigo' => '01',
            'CodigoTarifaIVA' => $this->ivaCode($rate),
            'Tarifa' => $rate,
            'Monto' => $monto,
        ];

        if ($exoneracion && $montoExoneracion > 0) {
            $exoneracionNode = [
                'TipoDocumentoEX1' => $exoneracion['tipo_documento'],
                'NumeroDocumento' => $exoneracion['numero_documento'],
                'NombreInstitucion' => $exoneracion['nombre_institucion'],
                'FechaEmisionEX' => $exoneracion['fecha_emision'],
                'TarifaExonerada' => (float) $exoneracion['tarifa_exonerada'],
                'MontoExoneracion' => $montoExoneracion,
            ];

            if (! empty($exoneracion['tipo_documento_otro'])) {
                $exoneracionNode['TipoDocumentoOTRO'] = $exoneracion['tipo_documento_otro'];
            }

            if (! empty($exoneracion['articulo'])) {
                $exoneracionNode['Articulo'] = $exoneracion['articulo'];
            }

            if (! empty($exoneracion['inciso'])) {
                $exoneracionNode['Inciso'] = $exoneracion['inciso'];
            }

            if (! empty($exoneracion['nombre_institucion_otros'])) {
                $exoneracionNode['NombreInstitucionOTRO'] = $exoneracion['nombre_institucion_otros'];
            }

            $impuesto['Exoneracion'] = $exoneracionNode;
        }

        return $impuesto;
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
