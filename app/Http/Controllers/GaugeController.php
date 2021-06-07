<?php

namespace App\Http\Controllers;

use App\Cancelacion;
use App\ConfiguracionCancelacion;
use App\HistorialCancelacion;
use App\HistorialTransferencia;
use App\Lecturas;
use App\Medidor;
use App\Persona;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Webpatser\Uuid\Uuid;

class GaugeController extends Controller
{
    public function listOfMetersByPartner($uid)
    {
        $validator = Validator::make(
            Persona::inputRulesPartnerExcept($uid),
            Persona::rulesPartnerExcept()
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
        $gauges = User::userGetForName($uid);
        if ($gauges)
        {
            $gauges = $gauges->gauges()
                ->selectRaw(<<<END
                    idMedidor as id,
                    CAST(ordenMedidor AS INT) as order_gauge,
                    numeroMedidor as number_gauge,
                    direccion as direction,
                    estado as state
                    END)
                ->orderBy('order_gauge')
                ->get();
            return response()->json([
                'success' => true,
                'message' => "Lista de medidores dado el uid del socio: {$uid}.",
                'data' => $gauges
            ], 200);
        }
        return response()->json([
            'success' => false,
            'message' => 'Error al cotejas el socio con los medidores.'
        ], 400);
    }

    public function updateGauge(Request $request, $key)
    {
        $validator = Validator::make(
            Medidor::inputRulesGaugeEdit($request->input('number'), $request->input('direction'), $request->input('state'), $request->input('measure'), $key),
            Medidor::rulesGaugeEdit($key)
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
        $gauge = Medidor::id($key)->first();
        $user = Auth::user();
        if ($gauge && $user)
        {
            $gauge->numeroMedidor = $request->input('number');
            $gauge->direccion = $request->input('direction');
            $gauge->estado = $request->input('state');
            $gauge->save();
            $measure = $request->input('measure');
            if ($measure !== null && $measure >= 0)
            {
                $reading = Lecturas::gauge($gauge->idMedidor)->orderByDesc('fechaMedicion')->first();
                if ($reading)
                {
                    Lecturas::_instanceAndSaving($gauge->idMedidor, $user->idUsuario, $measure, $reading->fechaMedicion, 'INITIAL');
                    return response()->json([
                        'success' => true,
                        'message' => 'Edición y reseteo de lectura completadas exitosamente.'
                    ], 200);
                }
                return response()->json([
                    'success' => true,
                    'message' => 'Error al cotejar la lectura.'
                ], 206);
            }
            return response()->json([
                'success' => true,
                'message' => 'Edición exitosa'
            ], 200);
        }
        return response()->json([
            'success' => false,
            'message' => 'Error al encontrar el medidor.'
        ], 400);
    }

    public function deleteGauge($number)
    {
        $validator = Validator::make(
            Medidor::inputRulesGaugeDelete($number),
            Medidor::rulesGaugeDelete()
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
        try
        {
            $gauge = Medidor::numberGauge($number)->first();
            if ($gauge)
            {
                $nonCanceledReadings = Medidor::existsPaymentGauge($gauge->numeroMedidor);
                if ($nonCanceledReadings === 0)
                {
                    $gauge->delete();
                    return response()->json([
                        'success' => true,
                        'message' => 'Medidor eliminado exitosamente.'
                    ], 200);
                }
                return response()->json([
                    'success' => false,
                    'message' => 'Existen lecturas pendientes que cancelar.'
                ], 400);
            }
            return response()->json([
                'success' => false,
                'message' => 'Error al cotejar el medidor.'
            ], 400);
        }
        catch (\Exception $e)
        {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el medidor.'
            ], 400);
        }
    }

    public function createGauge(Request $request, $uid)
    {
        $validator = Validator::make(
            Medidor::inputRulesGauges($request),
            Medidor::rulesGauges()
        );
        if ($validator->fails())
        {
            return response()->json([
                'success' => false,
                'message' => 'Formato incorrecto.',
                'errors' => $validator->errors()->messages()
            ], 400);
        }
        try
        {
            $key = Uuid::generate()->string;
            $validator = Validator::make(
                Cancelacion::inputRulesGaugeTransaction($request->input('compra'), $key),
                Cancelacion::rulesGaugeTransaction()
            );
            if ($validator->fails())
            {
                return response()->json([
                    'success' => false,
                    'message' => 'Formato incorrecto.',
                    'errors' => $validator->errors()->messages()
                ], 400);
            }
            $user = User::userGetForName($uid);
            $ultimateRegisterReading = Lecturas::select('fechaMedicion')->groupBy('fechaMedicion')->orderByDesc('fechaMedicion')->first();
            if ($user && $ultimateRegisterReading)
            {
                $dateLimit = Carbon::parse($ultimateRegisterReading->fechaMedicion);
                $dateLimit->setDay(1);
                $dateFlag = Carbon::parse($request->input('fechaNivelacion'));
                $dateFlag->setDay(1);
                if($dateFlag <= $dateLimit)
                {
                    Medidor::_instanceAndSaving($user->idUsuario, $request->input('numero'), $request->input('direccion'), $request->input('fechaInstalacion'), $request->input('estado'));
                    $gauge = Medidor::getDataUsingNumber($request->input('numero'));
                    $user_auth = Auth::user();
                    $configCancellations = ConfiguracionCancelacion::activeConfiguration();
                    if ($gauge && $user_auth && $configCancellations)
                    {
                        $gaugeReading = $request->input('lectura');
                        Lecturas::_instanceAndSaving($gauge->idMedidor, $user_auth->idUsuario, $gaugeReading, $request->input('fechaNivelacion'), 'INITIAL');
                        Lecturas::readingsToLevel($dateLimit, $dateFlag, $gaugeReading, $configCancellations, $gauge, $user_auth);
                        $cancellation = new Cancelacion();
                        $cancellation->prepareSaving($request->input('compra')['precio'], $key, $request->input('compra')['moneda'], $request->input('compra')['tipo']);
                        $transference = new HistorialTransferencia();
                        $transference->preparingSaving($user->idUsuario, $gauge->idMedidor, $cancellation->idCancelacion, $request->input('compra')['precio']);
                        return response()->json([
                            'success' => true,
                            'message' => 'Medidor agregado correctamente.'
                        ], 200);
                    }
                    return response()->json([
                        'success' => true,
                        'message' => 'No se pudo vincular al nuevo medidor, con la medida inicial. Comuniquese con soporte.'
                    ], 206);
                }
                return response()->json([
                    'success' => false,
                    'message' => 'FEcha de nivelación exedida.'
                ], 400);
            }
            return response()->json([
                'success' => false,
                'message' => 'No se pudo vincular al usuario.'
            ], 400);
        }
        catch (\Exception $e)
        {
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar.',
                'errors' => $e,
            ], 400);
        }
    }

}
