<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogActivityMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (auth()->check() && $request->isMethod('GET') && !$request->ajax() && !$request->is('audit-logs*') && !$request->is('run-migrate-temp')) {
            $routeName = $request->route() ? $request->route()->getName() : null;
            if ($routeName) {
                // Determine a description based on the route or path
                $desc = "Viewed page: " . $request->path();
                
                // Do not log simple profile views or similar if too noisy, but here we log everything named
                activity()
                    ->causedBy(auth()->user())
                    ->event('view')
                    ->log($desc);
            }
        }

        return $response;
    }
}
