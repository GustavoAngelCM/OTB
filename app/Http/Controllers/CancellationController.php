<?php

namespace App\Http\Controllers;

use App\Cancelacion;
use App\HistorialCancelacion;
use App\ConfiguracionCancelacion;
use App\Lecturas;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Webpatser\Uuid\Uuid;

class CancellationController extends Controller
{
    public function setCancellations(Request $request)
    {
        $user = Auth::user();
        if ($user->tipoUsuario_id == 1)
        {
            $changeCoin = Http::get('https://jsonplaceholder.typicode.com/users');
            $key = Uuid::generate()->string;
            $validator = Validator::make(
                [
                    'cancellations' => $request->input('cancellations'),
                    'keyCancelacion' => $key
                ],
                [
                    'cancellations' => 'bail|required',
                    'keyCancelacion' => 'bail|required|unique:cancelacions'
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
            else
            {
                return $changeCoin->body();

            }
//            else
//            {
//                $validator = null;
//                $error = false;
//                $total = 0;
//                $moneda = '';
//                $tipo = '';
//                foreach ($request->input('cancellations') as $clave => $valor)
//                {
//                    $validator = Validator::make(
//                        [
//                            'key' => $valor['key'],
//                            'monto' => $valor['monto'],
//                            'multa' => $valor['multa'],
//                            'moneda' => $valor['moneda'],
//                            'tipo' => $valor['tipo'],
//                        ],
//                        [
//                            'idLectura' => 'bail|required|numeric',
//                            'monto' => 'bail|required|numeric',
//                            'multa' => 'bail|required|numeric',
//                            'moneda' => 'bail|required',
//                            'tipo' => 'bail|required',
//                        ]
//                    );
//                    if ($validator->fails())
//                    {
//                        $error = true;
//                        break;
//                    }
//                    $total += $valor['monto'];
//                    $moneda = $valor['moneda'];
//                    $tipo = $valor['tipo'];
//                }
//                if ($error == true)
//                {
//                    return response()->json([
//                        'success' => false,
//                        'message' => 'Formato incorrecto.',
//                        'errors' => $validator->errors()->messages(),
//                    ], 400);
//                }
//                else
//                {
//                    $configCancellations = ConfiguracionCancelacion::where('activo', '=', 1)->orderBy('created_at', 'desc')->get()->first();
//                    $configDiscount = $configCancellations->descuentoCobroAgua;
//                    $discountArray = explode("=>", $configDiscount);
//                    $initialDateDiscount = Carbon::parse($discountArray[1]);
//                    $finalDateDiscount = Carbon::parse($discountArray[2]);
//                    $percentage = (int) $discountArray[3];
//                    $discount = false;
//                    $counterDiscount = 0;
//
//                    $requestCancellation = new Cancelacion();
//                    $requestCancellation->montoCancelacion = $total;
//                    $requestCancellation->keyCancelacion = $key;
//                    $requestCancellation->moneda = $moneda;
//                    $requestCancellation->tipoCancelacion = $tipo;
//                    $requestCancellation->save();
//
//                    $cancellationCurrent = Cancelacion::where('keyCancelacion', '=', $key)->get()->first();
//
//                    foreach ($request->input('cancellations') as $clave => $valor)
//                    {
//                        $historyPendingValid = HistorialCancelacion::where('lectura_id', $valor['key'])
//                            ->where('estadoMedicion',  '!=', 'CANCELLED')
//                            ->orderBy('created_at', 'desc')
//                            ->get()
//                            ->fisrt();
//
//                        $readingToFind = Lecturas::where('idLectura', $valor['key'])->select('fechaMedicion')->get()->first();
//
//                        if ( ($readingToFind->fechaMedicion >= $initialDateDiscount) && ($readingToFind->fechaMedicion <= $finalDateDiscount) )
//                        {
//                            $counterDiscount++;
//                            $discount = true;
//                        }
//                        else
//                        {
//                            $discount = false;
//                        }
//
//                        $requestHistoryCancellation = new HistorialCancelacion();
//                        $requestHistoryCancellation->lectura_id = $valor['key'];
//                        $requestHistoryCancellation->cancelacion_id = $cancellationCurrent->idCancelacion;
//                        $requestHistoryCancellation->diferenciaMedida = $historyPendingValid->diferenciaMedida;
//                        $requestHistoryCancellation->precioUnidad = $configCancellations->montoCuboAgua;
//                        $requestHistoryCancellation->subTotal = ($requestHistoryCancellation->diferenciaMedida * $requestHistoryCancellation->precioUnidad);
//                        $requestHistoryCancellation->montoCancelado = $valor['monto'];
//                        $requestHistoryCancellation->estadoMedicion = ($requestHistoryCancellation->montoCancelado < $requestHistoryCancellation->subTotal) ? ( ($discount && (($requestHistoryCancellation->subTotal * ((int) '0.'.$percentage)) == $requestHistoryCancellation->montoCancelado )) ? 'COMPLETED' : 'IN_PROCESS' ) : 'COMPLETED';
//                        $requestHistoryCancellation->save();
//
//                        if ($valor['multa'] > 0)
//                        {
//                            $requestHistoryCancellation = new HistorialCancelacion();
//                            $requestHistoryCancellation->lectura_id = $valor['key'];
//                            $requestHistoryCancellation->cancelacion_id = $cancellationCurrent->idCancelacion;
//                            $requestHistoryCancellation->diferenciaMedida = 0;
//                            $requestHistoryCancellation->precioUnidad = 0;
//                            $requestHistoryCancellation->subTotal = $valor['multa'];
//                            $requestHistoryCancellation->montoCancelado = $valor['multa'];
//                            $requestHistoryCancellation->estadoMedicion = 'COMPLETED';
//                            $requestHistoryCancellation->save();
//                        }
//
//                    }
//
//                    if ($counterDiscount > 0)
//                    {
//                        $requestCancellation->descuento = true;
//                        $requestCancellation->update();
//                    }
//                }
//            }
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
