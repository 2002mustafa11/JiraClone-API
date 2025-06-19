<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Debugbar;

class DebugbarMiddleware
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        if ($request->wantsJson() && $response instanceof JsonResponse) {
            $debugData = Debugbar::getData();

            $data = $response->getData(true);
            $data['_debugbar'] = $debugData;

            return response()->json($data);
        }

        return $response;
    }
}
