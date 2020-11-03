<?php

namespace App\Http\Controllers;

use App\Persona;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ReportsController extends Controller
{
    //
    public function partnerTransactions(Request $request)
    {
        $user = Auth::user();
        if ($user->tipoUsuario_id === 1)
        {
            $validator = Validator::make(
                Persona::inputRulesReport($request->input('month'), $request->input('payment'), $request->input('fine'), $request->input('assistance')),
                Persona::rulesReport()
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
            return response()->json([
                'success' => true,
                'data' => Persona::reportsManagement($request->input('month'), $request->input('payment'), $request->input('fine'), $request->input('assistance')),
                'message' => 'Reporte generado satisfactoriamente.',
            ], 200);
        }
        return response()->json([
            'success' => false,
            'message' => 'Credenciales insuficientes.',
        ], 401);
    }
}
