<?php

namespace App\Http\Controllers\Testing;

use Daniv04\HaciendaPackage\Models\Receipt;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReceiptsController
{
    public function __invoke(Request $request): JsonResponse
    {
        $receipts = Receipt::latest()
            ->limit(100)
            ->get()
            ->map(fn (Receipt $r) => [
                'id' => $r->id,
                'consecutive_number' => $r->consecutive_number,
                'receipt_type' => $r->receipt_type,
                'emission_date' => $r->emission_date?->format('d/m/Y H:i'),
                'emissor_name' => $r->emissor_name,
                'receiver_name' => $r->receiver_name,
                'total_voucher' => $r->total_voucher,
                'currency' => $r->currency,
                'receipt_status' => $r->receipt_status,
                'hacienda_status' => $r->hacienda_status,
            ]);

        return response()->json($receipts);
    }
}
