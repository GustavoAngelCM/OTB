<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Facades\JWTAuth;

class TokensController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('user', 'pass');
        $validator = Validator::make(
            $credentials,
            [
                'user' => 'required',
                'pass' => 'required',
            ]
        );
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Los campos no cumplen los requisitos mínimos.',
                'errors' => $validator->errors(),
            ], 422);
        } else {
            $credentials = [
                'name' => $credentials['user'],
                'password' => $credentials['pass'],
            ];
            $token = JWTAuth::attempt($credentials);
            if ($token) {
                return response()->json([
                    'success' => true,
                    'token' => $token,
                    'user' => User::select('idUsuario', 'tipoUsuario_id', 'persona_id', 'name', 'email', 'icoType')
                        ->where('name', $credentials['name'])
                        ->orWhere('email', $credentials['name'])
                        ->get()
                        ->first()
                ],200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Las credenciales son incorrectas.',
                    'errors' => $validator->errors(),
                ], 401);
            }
        }
    }

    public function refresh()
    {
        $token = JWTAuth::getToken();
        try
        {
            $token = JWTAuth::refresh($token);
            return response()->json([
                'success' => true,
                'token' => $token,
            ],200);
        }
        catch (JWTException $ex)
        {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener token.',
            ], 422);
        }
        catch (TokenExpiredException $ex)
        {
            return response()->json([
                'success' => false,
                'message' => 'Token ya expirado.',
            ], 422);
        }
        catch (TokenBlacklistedException $ex)
        {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo refrescar el token.',
            ], 422);
        }
    }

    public function verifyValidateToken()
    {
        return response()->json([
            'success' => true,
            'token' => JWTAuth::getToken(),
            'user' => Auth::user(),
        ], 200);
    }

    public function logout()
    {
        $token = JWTAuth::getToken();
        try
        {
            JWTAuth::invalidate($token);
            return response()->json([
                'success' => true,
                'message' => 'Sesión finalizada correctamente.',
            ],200);
        }
        catch (JWTException $e)
        {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo finalizar la sesión.',
            ], 422);
        }
    }

}
