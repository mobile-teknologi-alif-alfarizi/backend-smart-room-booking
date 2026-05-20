<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ExternalApiController extends Controller
{
    /**
     * Proxy to ShiftShift API
     */
    public function getShiftShiftInstance()
    {
        try {
            $response = Http::get('https://shiftshift.app/api/identity/instance');
            
            return response()->json($response->json(), $response->status());
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch from ShiftShift API',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
