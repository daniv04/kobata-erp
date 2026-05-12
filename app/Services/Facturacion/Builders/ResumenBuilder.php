<?php

namespace App\Services\Facturacion\Builders;

use App\Enums\Currency;
use App\Models\CabysCode;

class ResumenBuilder
{
    public function build(array $items, string $currency, array $paymentMethods): array
    {
        $servGravados = 0;
        $servExentos = 0;
        $mercGravadas = 0;
        $mercExentas = 0;
        $descuentos = 0;
        $impuesto = 0;
        $desglose = [];

        foreach ($items as $item) {
            $montoTotal = round($item['quantity'] * $item['unit_price'], 5);
            $descuento = $item['discount_enabled']
                ? round($montoTotal * (($item['discount_percentage'] ?? 0) / 100), 5)
                : 0;
            $netSubtotal = round($montoTotal - $descuento, 5);
            $taxRate = (float) ($item['tax_percentage'] ?? 13);
            $taxAmount = round($netSubtotal * ($taxRate / 100), 5);

            $descuentos += $descuento;
            $impuesto += $taxAmount;

            $isService = CabysCode::isService((string) ($item['cabys_code'] ?? ''));

            if ($taxRate > 0) {
                $isService ? $servGravados += $montoTotal : $mercGravadas += $montoTotal;
            } else {
                $isService ? $servExentos += $montoTotal : $mercExentas += $montoTotal;
            }

            $ivaCode = $this->ivaCode($taxRate);
            $groupKey = "01_{$ivaCode}";

            if (! isset($desglose[$groupKey])) {
                $desglose[$groupKey] = [
                    'Codigo' => '01',
                    'CodigoTarifaIVA' => $ivaCode,
                    'TotalMontoImpuesto' => 0,
                ];
            }
            $desglose[$groupKey]['TotalMontoImpuesto'] = round(
                $desglose[$groupKey]['TotalMontoImpuesto'] + $taxAmount,
                5
            );
        }

        $totalGravado = round($servGravados + $mercGravadas, 5);
        $totalExento = round($servExentos + $mercExentas, 5);
        $totalVenta = round($totalGravado + $totalExento, 5);
        $totalVentaNeta = round($totalVenta - $descuentos, 5);
        $totalComprobante = round($totalVentaNeta + $impuesto, 5);

        $currencyEnum = Currency::from($currency);

        return [
            'ResumenFactura' => [
                'CodigoTipoMoneda' => [
                    'CodigoMoneda' => $currencyEnum->value,
                    'TipoCambio' => $currencyEnum->exchangeRate(),
                ],
                'TotalServGravados' => round($servGravados, 5),
                'TotalServExentos' => round($servExentos, 5),
                'TotalServExonerado' => 0,
                'TotalServNoSujeto' => 0,
                'TotalMercanciasGravadas' => round($mercGravadas, 5),
                'TotalMercanciasExentas' => round($mercExentas, 5),
                'TotalMercExonerada' => 0,
                'TotalMercNoSujeta' => 0,
                'TotalGravado' => $totalGravado,
                'TotalExento' => $totalExento,
                'TotalExonerado' => 0,
                'TotalNoSujeto' => 0,
                'TotalVenta' => $totalVenta,
                'TotalDescuentos' => round($descuentos, 5),
                'TotalVentaNeta' => $totalVentaNeta,
                'TotalImpuesto' => round($impuesto, 5),
                'TotalImpAsumEmisorFabrica' => 0,
                'TotalIVADevuelto' => 0,
                'TotalOtrosCargos' => 0,
                'TotalComprobante' => $totalComprobante,
                'TotalDesgloseImpuesto' => array_values($desglose),
                'MedioPago' => $this->buildMedioPago($paymentMethods),
            ],
        ];
    }

    /** @param array<int, array{type: string, amount: float|int, others_description?: string|null}> $paymentMethods */
    private function buildMedioPago(array $paymentMethods): array
    {
        return array_map(function (array $method) {
            $entry = [
                'TipoMedioPago' => $method['type'],
                'TotalMedioPago' => round((float) $method['amount'], 5),
            ];

            if (($method['others_description'] ?? null) !== null) {
                $entry['MedioPagoOtros'] = $method['others_description'];
            }

            return $entry;
        }, $paymentMethods);
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
