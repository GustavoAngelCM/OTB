<?php

namespace App\Http\Controllers;

use App\User;
use App\Persona;
use Illuminate\Support\Facades\Auth;

class PartnersController extends Controller
{
    public function getPartners()
    {
        $user = Auth::user();
        if ($user->tipoUsuario_id == 1)
        {
            $partners = [];
            $partnersDB = Persona::selectRaw('ci, count(medidors.numeroMedidor) as cantidadMedidores')
                ->join('users', 'personas.idPersona', '=', 'users.persona_id')
                ->join('medidors', 'users.idUsuario', '=', 'medidors.usuario_id')
                ->where('users.tipoUsuario_id', '!=', 1)
                ->groupBy('ci')
                ->orderBy('pNombre')
                ->get();
            foreach ($partnersDB as $value)
            {
                $name = Persona::where('ci', $value['ci'])->selectRaw('concat_ws(" ", pNombre, sNombre, apellidoP, apellidoM) as fullName, concat_ws(" ", pNombre, apellidoP) as shortName')->get()->first();
                $username = Persona::where('ci', $value['ci'])->get()->first()->user()->select('name', 'icoType')->get()->first();
                $partners[] = [
                    'fullName' => $name->fullName,
                    'shortName' => $name->shortName,
                    'uid' => $username->name,
                    'ico' => $username->icoType,
                    'gauges' => $value['cantidadMedidores'],
                    'gaugesProps' => Persona::where('ci', $value['ci'])->get()->first()->user()->get()->first()->gauges()->select('ordenMedidor as orden', 'numeroMedidor as numero')->get()
                ];
            }
            return $partners;
        }
        else
        {
            return response()->json([
                'success' => false,
                'message' => 'Credenciales insuficientes.',
            ], 401);
        }
    }

    public function getHistoryCancelled($uid)
    {
        $user = Auth::user();
        if ($user->tipoUsuario_id == 1)
        {
            $arrayToReadingsForGauge = [];
            try
            {
                $arrayToReadingsForGauge[] = User::personGet_userGetForName(User::userGetForName($uid));
                $gauges = User::gaugesGet_userGetForName(User::userGetForName($uid));
                foreach ($gauges as $gauge)
                {
                    $readings = $gauge->readings()->reorder('fechaMedicion', 'asc')->get();
                    $arrayToReadings = [];
                    foreach ($readings as $reading)
                    {
                        $histories = $reading->historyAll()->orderBy('fechaHoraHCancelacion')->get();
                        $arrayToHistories = [];
                        foreach ($histories as $history)
                        {
                            $index_to_cancellation = $history->cancellation()
                                ->select(
                                    'keyCancelacion as codigo',
                                    'montoCancelacion as monto',
                                    'moneda',
                                    'fechaCancelacion as fecha',
                                    'descartado',
                                    'descuento'
                                )
                                ->get()->first();
                            $history_and_cancellation = [
                                'idHistorialCancelaciones' => $history->idHistorialCancelaciones,
                                'lectura_id' => $history->lectura_id,
                                'cancelacion_id' => $history->cancelacion_id,
                                'diferenciaMedida' => $history->diferenciaMedida,
                                'precioUnidad' => $history->precioUnidad,
                                'subTotal' => $history->subTotal,
                                'montoCancelado' => $history->montoCancelado,
                                'fechaHoraHCancelacion' => $history->fechaHoraHCancelacion,
                                'estadoMedicion' => $history->estadoMedicion,
                                'data_cancellation' => $index_to_cancellation
                            ];
                            $arrayToHistories[] = $history_and_cancellation;
                        }
                        $reading_to_histories = [
                            "idLectura" =>  $reading->idLectura,
                            "medidor_id" =>  $reading->medidor_id,
                            "usuario_id" =>  $reading->usuario_id,
                            "medida" =>  $reading->medida,
                            "fechaMedicion" =>  $reading->fechaMedicion,
                            "estado" =>  $reading->estado,
                            "historialLectura" => $arrayToHistories
                        ];
                        $arrayToReadings[] = $reading_to_histories;
                    }
                    $gauges_to_reading = [
                        'idMedidor' => $gauge->idMedidor,
                        'usuario_id' => $gauge->usuario_id,
                        'ordenMedidor' => $gauge->ordenMedidor,
                        'numeroMedidor' => $gauge->numeroMedidor,
                        'direccion' => $gauge->direccion,
                        'fechaInstalacion' => $gauge->fechaInstalacion,
                        'estado' => $gauge->estado,
                        'lecturas' => $arrayToReadings
                    ];
                    $arrayToReadingsForGauge[] = $gauges_to_reading;
                }
                return $arrayToReadingsForGauge;
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
