<?php

namespace App\Http\Controllers;

use App\TipoUsuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TipoController extends Controller
{

    public function list()
    {
        return TipoUsuario::select('idTipoUsuario', 'nombreTipoUsuario')->where('nombreTipoUsuario', '!=', 'ADMINISTRADOR')->get();
    }

    public function create(Request $request)
    {
        $validator = Validator::make(
            [
                'nombreTipoUsuario' => $request->input('tipo'),
            ],
            [
                'nombreTipoUsuario' => 'bail|required|unique:tipo_usuarios|min:2|max:50',
            ]
        );
        $errors = $validator->errors();
        if ($validator->fails())
        {
            return response()->json([
                'success' => false,
                'message' => 'Formato incorrecto.',
                'errors' => $errors->messages(),
            ], 400);
        }
        $tipoRequest = new TipoUsuario();
        $tipoRequest->nombreTipoUsuario = strtoupper($request->input('tipo'));
        $tipoRequest->save();
        return response()->json([
            'success' => true,
            'message' => 'Registro creado exitosamente.',
            'errors' => null,
        ], 201);
    }

}
