<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\AdminHistory;

class TrackAdminHistory
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        AdminHistory::create([
            'user_id' => auth()->id(),
            'ip'      => $this->getClientIp($request),
            'method'  => $request->method(),
            'client'  => $request->userAgent(),
            'url'     => $request->fullUrl(),
        ]);

        return $response;
    }

    /**
     * Get the real client IP.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    private function getClientIp(Request $request)
    {
        $cfIp = $request->header('CF-Connecting-IP');
        if ($cfIp) {
            return $cfIp;
        }

        $forwarded = $request->header('X-Forwarded-For');
        if ($forwarded) {
            $ips = explode(',', $forwarded);
            return trim($ips[0]);
        }

        return $request->ip();
    }
}
