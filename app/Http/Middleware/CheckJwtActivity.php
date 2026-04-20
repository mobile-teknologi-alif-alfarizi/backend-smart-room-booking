<?php

namespace App\Http\Middleware;

use App\Models\JwtSession;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;

class CheckJwtActivity
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // Get the JWT token
            $token = JWTAuth::parseToken();
            $payload = $token->getPayload();
            $jti = $payload->get('jti');

            // Find the session
            $session = JwtSession::where('jti', $jti)->first();

            if (!$session) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session tidak ditemukan',
                ], 401);
            }

            // Check if session is still active (within 12 hours of inactivity)
            if (!$session->isActive()) {
                // Session has been inactive for more than 12 hours
                $session->delete();

                return response()->json([
                    'success' => false,
                    'message' => 'Session Anda telah expired karena tidak ada aktivitas selama 12 jam. Silakan login kembali.',
                ], 401);
            }

            // Update last activity
            $session->updateActivity();

            return $next($request);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: ' . $e->getMessage(),
            ], 401);
        }
    }
}
