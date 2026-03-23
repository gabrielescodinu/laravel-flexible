<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasPermission
{
    /**
     * @param  list<string>  $permissions
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();
        if (! $user) {
            abort(403);
        }

        $normalizedPermissions = collect($permissions)
            ->flatMap(static fn (string $value): array => explode(',', $value))
            ->map(static fn (string $value): string => trim($value))
            ->filter(static fn (string $value): bool => $value !== '')
            ->values()
            ->all();

        if ($normalizedPermissions === [] || $user->hasAnyPermission($normalizedPermissions)) {
            return $next($request);
        }

        abort(403, 'Non hai i permessi necessari per accedere a questa sezione.');
    }
}
