<?php

namespace App\Enums;

enum StockMovementType: string
{
    case Purchase = 'purchase';
    case SaleOut = 'sale_out';
    case Adjustment = 'adjustment';
    case TransferIn = 'transfer_in';
    case TransferOut = 'transfer_out';
    case ConsignmentOut = 'consignment_out';
    case ConsignmentReturn = 'consignment_return';
    case CreditOut = 'credit_out';
    case CreditReturn = 'credit_return';
    case SaleReturn = 'sale_return';
    case InitialStock = 'initial_stock';

    public function label(): string
    {
        return match ($this) {
            self::Purchase => 'Recepción de compras',
            self::SaleOut => 'Salida confirmada por guía de retiro',
            self::Adjustment => 'Ajuste manual',
            self::TransferIn => 'Recepción de traslado',
            self::TransferOut => 'Despacho de traslado',
            self::ConsignmentOut => 'Salida por consignación',
            self::ConsignmentReturn => 'Devolución de consignación',
            self::CreditOut => 'Salida por crédito',
            self::CreditReturn => 'Devolución de crédito',
            self::SaleReturn => 'Devolución por nota de crédito',
            self::InitialStock => 'Stock inicial',
        };
    }
}
