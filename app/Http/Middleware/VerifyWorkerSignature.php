<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class VerifyWorkerSignature
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $signature = $request->header('X-Worker-Signature');
        $secret = env('WORKER_SECRET');

        // Fail Close: If the secret is not configured on the server, reject access immediately.
        if (empty($secret)) {
            Log::warning('[Security] WORKER_SECRET is not configured on the server. Rejecting request to prevent bypass.');
            return response()->json(['error' => 'Unauthorized. Server security configuration missing.'], 401);
        }

        // Check if the signature is provided
        if (empty($signature)) {
            return response()->json(['error' => 'Unauthorized. Missing security signature.'], 401);
        }

        // Timing Attack Mitigation: Use hash_equals for constant-time string comparison
        if (!hash_equals($secret, $signature)) {
            Log::warning('[Security] Invalid X-Worker-Signature signature attempt.', [
                'ip' => $request->ip(),
                'path' => $request->path(),
            ]);
            return response()->json(['error' => 'Unauthorized. Invalid signature.'], 401);
        }

        return $next($request);
    }
}
