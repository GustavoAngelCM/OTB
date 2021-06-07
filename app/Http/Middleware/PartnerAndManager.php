<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PartnerAndManager
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        if ($user && ($user->tipoUsuario_id === 2 || $user->tipoUsuario_id === 3))
        {
            return $next($request);
        }
        return response()->json([
            'success' => false,
            'message' => 'No tiene permitido realizar este tipo de acciones.'
        ], 401);
    }
}
