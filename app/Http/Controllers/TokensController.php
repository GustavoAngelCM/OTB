<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
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
                    'user' => User::where('name', $credentials['name'])->get()->first()
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
}
