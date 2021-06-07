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
use Webpatser\Uuid\Uuid;

class CancellationController extends Controller
{
    public function setCancellations(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        if ($user && $user->tipoUsuario_id === 1)
        {
            try
            {
                $key = Uuid::generate()->string;
                $validator = Validator::make(
                    Cancelacion::inputRulesCancellation($request->input('cancellations'), $key),
                    Cancelacion::rulesCancellation()
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

                $validator = null;
                $error = false;
                $total = 0;
                $moneda = '';
                $tipo = '';
                foreach ($request->input('cancellations') as $clave => $valor)
                {
                    $validator = Validator::make(
                        Cancelacion::inputRulesBindingToReading($valor['key'], $valor['monto'], $valor['multa'], $valor['moneda'], $valor['tipo']),
                        Cancelacion::rulesBindingToReading()
                    );
                    if ($validator->fails())
                    {
                        $error = true;
                        break;
                    }
                    $total += ($valor['monto'] + $valor['multa']);
                    $moneda = $valor['moneda'];
                    $tipo = $valor['tipo'];
                }
                if ($error === true)
                {
                    return response()->json([
                        'success' => false,
                        'message' => 'Formato incorrecto.',
                        'errors' => $validator->errors()->messages(),
                    ], 400);
                }

                $configCancellations = ConfiguracionCancelacion::activeConfiguration();
                $configDiscount = $configCancellations->descuentoCobroAgua;
                $discountArray = ($configDiscount === null) ? null : explode("=>", $configDiscount);
                $initialDateDiscount = ($configDiscount === null) ? null : Carbon::parse($discountArray[1]);
                $finalDateDiscount = ($configDiscount === null) ? null : Carbon::parse($discountArray[2]);
                $percentage = ($configDiscount === null) ? null : (int) $discountArray[3];

                $counterDiscount = 0;

                $requestCancellation = new Cancelacion();
                $requestCancellation->prepareSaving($total, $key, $moneda, $tipo);

                $cancellationCurrent = $requestCancellation->getDataCancellation();

                foreach ($request->input('cancellations') as $clave => $valor)
                {
                    $historyPendingValid = HistorialCancelacion::pendingValid($valor['key']);

                    $readingToFind = Lecturas::findReading($valor['key']);

                    if ( ($readingToFind->fechaMedicion >= $initialDateDiscount) && ($readingToFind->fechaMedicion <= $finalDateDiscount)  && $configDiscount !== null)
                    {
                        $counterDiscount++;
                    }

                    $amountChanged = $requestCancellation->changeCoin($requestCancellation->moneda, $valor['monto']);

                    $subTotal = Cancelacion::subTotalLogicCancellation($historyPendingValid->diferenciaMedida, $configCancellations->montoCuboAgua, $configCancellations->montoMinimoCancelacion);

                    $state = Cancelacion::statePercentageLogicCancellation($amountChanged + Cancelacion::calculatedTotalCancelled($valor['key']), $subTotal, $counterDiscount>0, $percentage);

                    HistorialCancelacion::_instanceAndSaving(
                        $valor['key'],
                        $cancellationCurrent->numero,
                        $historyPendingValid->diferenciaMedida,
                        $configCancellations->montoCuboAgua,
                        $subTotal,
                        $amountChanged,
                        $state
                    );

                    if ($valor['multa'] > 0)
                    {
                        $historyPendingValid = HistorialCancelacion::pendingValidFine($valor['key']);
                        HistorialCancelacion::_instanceAndSaving(
                            $valor['key'],
                            $cancellationCurrent->numero,
                            0,
                            0,
                            $historyPendingValid->subTotal,
                            $configCancellations->montoMultaConsumoAgua,
                            'COMPLETED'
                        );
                    }
                    else if  ( ($readingToFind->fechaMedicion >= $initialDateDiscount) && ($readingToFind->fechaMedicion <= $finalDateDiscount) )
                        {
                            $historyPendingValid = HistorialCancelacion::pendingValidFine($valor['key']);
                            if ($historyPendingValid)
                            {
                                HistorialCancelacion::_instanceAndSaving(
                                    $valor['key'],
                                    $cancellationCurrent->numero,
                                    0,
                                    0,
                                    $historyPendingValid->subTotal,
                                    0,
                                    'COMPLETED'
                                );
                            }
                        }

                }

                if ($counterDiscount > 0)
                {
                    $requestCancellation->descuento = true;
                    $requestCancellation->update();
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Cancelación realizada exitosamente.',
                    'data' => [
                        'cancel_data' => $requestCancellation->getDataCancellation(),
                        'months' => Cancelacion::mountAndFineDataBindingHistoryForCancellation($key),
                        'partner'=> $requestCancellation->getDataPartnerReadingToCancellation()
                    ]
                ], 200);
            }
            catch (\Exception $e)
            {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo completar la solicitud',
                    "error" => $e
                ], 400);
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

    public function printCancellation(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make(
            Cancelacion::inputRulesPrint($request->input('codigo')),
            Cancelacion::rulesPrint()
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
        $cancellation = new Cancelacion();
        $cancellation->keyCancelacion = $request->input('codigo');
        return response()->json([
            'success' => true,
            'message' => 'Reimpresión realizada correctamente .',
            'data' => [
                'cancel_data' => $cancellation->getDataCancellation(),
                'months' => Cancelacion::mountAndFineDataBindingHistoryForCancellation($cancellation->keyCancelacion),
                'partner'=> $cancellation->getDataPartnerReadingToCancellation()
            ]
        ], 200);
    }

    public function exchangeRate(): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        if ($user->tipoUsuario_id === 1)
        {
            $changesCoins = new \stdClass();
            $changesCoins->BOLIVIANOS = Cancelacion::changeCoinAPI('https://api.cambio.today/v1/full/BOB/json?key=4234|S^9b_2vNDkPjc~eR1Dr^4q3Y2fZfJxAA', 'USD', 'EUR', 'DOLARES', 'EUROS');
            $changesCoins->EUROS = Cancelacion::changeCoinAPI('https://api.cambio.today/v1/full/EUR/json?key=4234|S^9b_2vNDkPjc~eR1Dr^4q3Y2fZfJxAA', 'USD', 'BOB', 'DOLARES', 'BOLIVIANOS');
            $changesCoins->DOLARES = Cancelacion::changeCoinAPI('https://api.cambio.today/v1/full/USD/json?key=4234|S^9b_2vNDkPjc~eR1Dr^4q3Y2fZfJxAA', 'BOB', 'EUR', 'BOLIVIANOS', 'EUROS');;
            return response()->json($changesCoins);
        }

        return response()->json([
            'success' => false,
            'message' => 'Credenciales insuficientes.',
        ], 401);
    }

    public function history()
    {
        $user = Auth::user();
        if ($user->tipoUsuario_id === 1)
        {
            return Cancelacion::where('descartado', 0)
                ->orderBy('idCancelacion', 'desc')->
                limit(50)
                ->select([
                'idCancelacion',
                'montoCancelacion as mount',
                'fechaCancelacion as date',
                'keyCancelacion as code',
                'descuento as discount',
                'moneda as coin',
                'tipoCancelacion as typeCancellation'
            ])->with(['historyCancellation', 'historyAssists', 'historyProBackground', 'historyProTransfers'])->get();
        }
        return response()->json([
            'success' => false,
            'message' => 'Credenciales insuficientes.',
        ], 401);
    }

    public function cancelTransaction($key): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        if ($user->tipoUsuario_id === 1)
        {
            try
            {
                Cancelacion::CancelTransaction($key);
                $cancelPrev = Cancelacion::getIDCancellation($key);
                $historyForCancellation = HistorialCancelacion::where('cancelacion_id', $cancelPrev->idCancelacion)->get();
                foreach ($historyForCancellation as $history)
                {
                    HistorialCancelacion::_instanceAndSaving($history->lectura_id, $history->cancelacion_id, $history->diferenciaMedida, $history->precioUnidad, $history->subTotal, $history->montoCancelado, 'CANCELLED');
                }
                foreach ($historyForCancellation as $history)
                {
                    HistorialCancelacion::_instanceAndSaving($history->lectura_id, null, $history->diferenciaMedida, $history->precioUnidad, $history->subTotal, 0, 'PENDING');
                }
                return response()->json([
                    'success' => true,
                    'message' => 'Transacción realizada satisfactoriamente.',
                ], 200);
            }
            catch (\Error $e)
            {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de solicitud',
                ], 400);
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
