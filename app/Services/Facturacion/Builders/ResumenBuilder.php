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
        $servExonerados = 0;
        $mercGravadas = 0;
        $mercExentas = 0;
        $mercExoneradas = 0;
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

            $exoneracion = $item['exoneracion'] ?? null;
            $montoExoneracion = 0;

            if ($exoneracion && $taxRate > 0) {
                $montoExoneracion = round(((float) $exoneracion['tarifa_exonerada'] / 100) * $netSubtotal, 5);
            }

            $impuestoNeto = round($taxAmount - $montoExoneracion, 5);

            $descuentos += $descuento;
            $impuesto += $impuestoNeto;

            $isService = CabysCode::isService((string) ($item['cabys_code'] ?? ''));

            if ($taxRate == 0) {
                $isService ? $servExentos += $montoTotal : $mercExentas += $montoTotal;
            } elseif ($montoExoneracion > 0) {
                // Porcentaje de exoneración sobre el total de la línea
                $porcentajeEx = $taxAmount > 0 ? $montoExoneracion / $taxAmount : 0;
                $montoGravado = round($montoTotal * (1 - $porcentajeEx), 5);
                $montoExoneradoLinea = round($montoTotal * $porcentajeEx, 5);

                if ($isService) {
                    $servGravados += $montoGravado;
                    $servExonerados += $montoExoneradoLinea;
                } else {
                    $mercGravadas += $montoGravado;
                    $mercExoneradas += $montoExoneradoLinea;
                }
            } else {
                $isService ? $servGravados += $montoTotal : $mercGravadas += $montoTotal;
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
                $desglose[$groupKey]['TotalMontoImpuesto'] + $impuestoNeto,
                5
            );
        }

        $totalGravado = round($servGravados + $mercGravadas, 5);
        $totalExento = round($servExentos + $mercExentas, 5);
        $totalExonerado = round($servExonerados + $mercExoneradas, 5);
        $totalVenta = round($totalGravado + $totalExento + $totalExonerado, 5);
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
                'TotalServExonerado' => round($servExonerados, 5),
                'TotalServNoSujeto' => 0,
                'TotalMercanciasGravadas' => round($mercGravadas, 5),
                'TotalMercanciasExentas' => round($mercExentas, 5),
                'TotalMercExonerada' => round($mercExoneradas, 5),
                'TotalMercNoSujeta' => 0,
                'TotalGravado' => $totalGravado,
                'TotalExento' => $totalExento,
                'TotalExonerado' => $totalExonerado,
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
