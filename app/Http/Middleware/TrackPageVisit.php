<?php

namespace App\Http\Middleware;

use App\Models\PageVisit;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Records a page visit for analytics purposes.
 *
 * Deduplicated per IP + page key within a 10-minute window so that
 * page refreshes do not inflate counts. Admin users are excluded.
 *
 * Usage in routes:  ->middleware('track.page:home')
 */
class TrackPageVisit
{
    public function handle(Request $request, Closure $next, string $pageKey): Response
    {
        $response = $next($request);

        // Only count successful, non-admin, non-bot GET requests
        if (
            $request->isMethod('GET') &&
            $response->isSuccessful() &&
            ! $this->isAdmin()
        ) {
            $ip       = $request->ip() ?? 'unknown';
            $lockKey  = "pv:{$pageKey}:{$ip}";

            if (! Cache::has($lockKey)) {
                Cache::put($lockKey, 1, 600); // 10-minute dedup window

                PageVisit::create([
                    'page_key'    => $pageKey,
                    'ip_address'  => $ip,
                    'designer_id' => Auth::guard('designer')->id(),
                ]);
            }
        }

        return $response;
    }

    private function isAdmin(): bool
    {
        $user = Auth::guard('designer')->user();
        return $user && $user->is_admin;
    }
}
