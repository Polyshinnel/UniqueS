<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Если пользователь не авторизован и пытается получить доступ к защищенным страницам
        if (!Auth::check() && !$request->is('login')) {
            return redirect()->route('login');
        }

        // Если пользователь авторизован и пытается получить доступ к странице входа
        if (Auth::check() && $request->is('login')) {
            return redirect('/guide');
        }

        return $next($request);
    }
} 