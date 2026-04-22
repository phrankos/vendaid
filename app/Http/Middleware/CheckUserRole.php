<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();
        // Log::channel('stderr')->Info($roles);
        // Log::channel('stderr')->Info($request);
        $required_roles = array_map('intval', explode('|', $roles));
        
        if (in_array($user->role_id, $required_roles)) {
            return $next($request);
        }

        abort(403, 'Unauthorized action.');
    }
}
