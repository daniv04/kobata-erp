<?php

namespace App\Http\Controllers\Facturacion;

use App\Models\Canton;
use App\Models\District;
use App\Models\Neighborhood;
use App\Models\Province;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LocationController
{
    public function provinces(): JsonResponse
    {
        $provinces = Province::orderBy('name')->get(['id', 'name']);

        return response()->json($provinces);
    }

    public function cantons(Request $request): JsonResponse
    {
        $cantons = Canton::where('province_id', $request->query('province_id'))
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($cantons);
    }

    public function districts(Request $request): JsonResponse
    {
        $districts = District::where('canton_id', $request->query('canton_id'))
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($districts);
    }

    public function neighborhoods(Request $request): JsonResponse
    {
        $neighborhoods = Neighborhood::where('district_id', $request->query('district_id'))
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($neighborhoods);
    }
}
