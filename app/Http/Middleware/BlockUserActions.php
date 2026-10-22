<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockUserActions
{
    /**
     * Handle an incoming request.
     *
     * Temporarily restricts modifying actions for members while keeping
     * all views, menus, login, and registration fully accessible.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. If action restriction is disabled via config / .env, proceed normally
        if (!config('app.restrict_user_actions', env('RESTRICT_USER_ACTIONS', true))) {
            return $next($request);
        }

        // 2. Allow all safe read-only methods (GET, HEAD)
        // This ensures Dashboard, Network Menus, Reports, Profile, and Announcements remain viewable
        if (in_array($request->method(), ['GET', 'HEAD'])) {
            return $next($request);
        }

        // 3. Allow logout so users are never trapped in a session
        if ($request->routeIs('member.logout') || $request->is('logout') || $request->is('member/logout')) {
            return $next($request);
        }

        $message = "This action is under process, It will be available soon.";

        // 4. Handle AJAX / Fetch / JSON requests (e.g. OTP requests)
        if ($request->expectsJson() || $request->isXmlHttpRequest() || $request->is('api/*')) {
            return response()->json([
                'status' => false,
                'success' => false,
                'message' => $message,
            ], 200);
        }

        // 5. Handle standard form submissions (POST, PUT, PATCH, DELETE)
        return redirect()->route('member.action_blocked')->with('warning', $message);
    }
}
