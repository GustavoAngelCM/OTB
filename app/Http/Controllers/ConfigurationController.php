<?php

namespace App\Http\Controllers;

use App\ConfiguracionCancelacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ConfigurationController extends Controller
{
    public function getConfigurationState()
    {
        $cancellation = ConfiguracionCancelacion::activeConfiguration(true);
        $discountWeather = explode("=>", $cancellation->descuentoAgua);
        $cancellation->descuentoAgua = [
            'title' => $discountWeather[0],
            'dateInit' => $discountWeather[1],
            'dateEnd' => $discountWeather[2],
            'mount' => $discountWeather[3]
        ];
        return $cancellation;
    }

    public function setConfiguration(Request $request)
    {
        $user = Auth::user();
        if ($user->tipoUsuario_id == 1)
        {
            $validator = Validator::make(
                [
                    'montoCuboAgua' => $request->input('montoCubo'),
                    'montoMultaConsumoAgua' => $request->input('multaConsumo'),
                    'montoTransferenciaAccion' => $request->input('transferenciaAccion'),
                    'montoMinimoCancelacion' => $request->input('montoMinimo'),
                    'descuentoCobroAgua' => $request->input('descuentoAgua'),
                    'cantidadMesesParaMulta' => $request->input('mesesMulta'),
                ],
                [
                    'montoCuboAgua' => 'required|numeric',
                    'montoMultaConsumoAgua' => 'required|numeric',
                    'montoTransferenciaAccion' => 'required|numeric',
                    'montoMinimoCancelacion' => 'required|numeric',
                    'descuentoCobroAgua' => 'required',
                    'cantidadMesesParaMulta' => 'required|numeric',
                ]
            );
            $error = $validator->errors();
            if ($validator->fails())
            {
                return response()->json([
                    'success' => false,
                    'message' => 'No se cumple con los requisitos mínimos',
                    'errors' => $error->messages(),
                ], 422);
            }
            else
            {
                try
                {
                    ConfiguracionCancelacion::where('activo', true)->update(['activo' => false]);

                    $requestConfigureCancellation = new ConfiguracionCancelacion();
                    $requestConfigureCancellation->usuario_id = $user->idUsuario;
                    $requestConfigureCancellation->montoCuboAgua = $request->input('montoCubo');
                    $requestConfigureCancellation->montoMultaConsumoAgua = $request->input('multaConsumo');
                    $requestConfigureCancellation->montoTransferenciaAccion = $request->input('transferenciaAccion');
                    $requestConfigureCancellation->montoMinimoCancelacion = $request->input('montoMinimo');
                    $requestConfigureCancellation->descuentoCobroAgua = $request->input('descuentoAgua');
                    $requestConfigureCancellation->cantidadMesesParaMulta = $request->input('mesesMulta');
                    $requestConfigureCancellation->activo = true;
                    $requestConfigureCancellation->save();
                }
                catch (\Error $e)
                {
                    return response()->json([
                        'success' => false,
                        'message' => 'Error al registrar la configuración'
                    ], 422);
                }
            }
        }
        else
        {
            return response()->json([
                'success' => false,
                'message' => 'Credenciales insuficientes.',
            ], 401);
        }
    }
}
