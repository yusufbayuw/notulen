<?php

namespace App\Http\Middleware;

use App\Models\Organization;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureWorkspaceMember
{
    public function handle(Request $request, Closure $next): Response
    {
        $organization = $request->route('organization');

        if (! $organization instanceof Organization ||
            ! $request->user()?->organizations()->whereKey($organization->getKey())->exists()) {
            abort(404);
        }

        return $next($request);
    }
}
