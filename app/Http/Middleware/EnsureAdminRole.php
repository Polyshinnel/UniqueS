<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // Проверяем, что пользователь авторизован
        if (!$user) {
            return redirect('/login');
        }

        // Загружаем связь role, если она еще не загружена
        if (!$user->relationLoaded('role')) {
            $user->load('role');
        }

        // Проверяем, что у пользователя есть роль и она является администратором
        if (!$user->role || $user->role->name !== 'Администратор') {
            return redirect('/company');
        }

        return $next($request);
    }
}

