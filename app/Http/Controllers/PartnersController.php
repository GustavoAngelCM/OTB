<?php

namespace App\Http\Controllers;

use App\Cancelacion;
use App\ConfiguracionCancelacion;
use App\HistorialCancelacion;
use App\HistorialTransferencia;
use App\Medidor;
use App\Telefono;
use App\User;
use App\Persona;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Webpatser\Uuid\Uuid;

class PartnersController extends Controller
{
    public function getPartners(): array
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
                'gaugesProps' => Persona::where('ci', $value['ci'])->get()->first()->user()->get()->first()->gauges()->select('ordenMedidor as orden', 'numeroMedidor as numero', 'idMedidor as id')->get()
            ];
        }
        return $partners;
    }

    public function updatePartner(Request $request, $uid): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        $person = User::personGetForName($uid);
        if ($user && $person && $user->tipoUsuario_id === 1)
        {
            $validator = Validator::make(
                Persona::inputRulesUpdate(
                    $uid,
                    $request->input('tipo'),
                    $request->input('nombres'),
                    $request->input('apellidos'),
                    explode(' ', $request->input('ci'))[0],
                    $request->input('fechaNacimiento'), $request->input('sexo'),
                    $request->input('email'), $request->input('ico'),
                    $request->input('telefonos')
                ),
                Persona::rulesUpdate($person->idPersona)
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
            $idUser = User::userGetForName($uid)->idUsuario;

            Persona::updatingFields($person->idPersona, $request->input());
            User::updatingFields($idUser, $request->input());
            $phones = $request->input('telefonos');
            try
            {
                if ($phones !== null)
                {
                    $newNumber = new Telefono();
                    if ( count($person->phones) > 0)
                    {
                        Telefono::where('persona_id', $person->idPersona)->delete();
                        $newNumber->preparingSaving($person->idPersona, $request->input('telefonos'));
                    }
                    else
                    {
                        $newNumber->preparingSaving($person->idPersona, $request->input('telefonos'));
                    }
                }
                return response()->json([
                    'success' => true,
                    'message' => 'Registros actualizados correctamente.',
                ], 200);
            }
            catch (\Exception $e)
            {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar datos del telefono.',
                ], 400);
            }
        }
        return response()->json([
            'success' => false,
            'message' => 'Credenciales insuficientes.',
        ], 401);
    }

    public function getPartner($uid)
    {
        $user = Auth::user();
        if ($user && $user->tipoUsuario_id === 1)
        {
            $userData = User::uid($uid)->with(['person', 'person.phones'])->first();
            return response()->json([
                'success' => true,
                'data' => [
                    "names" => $userData->person->names(),
                    "lastnames" => $userData->person->lastNames(),
                    "birthdate" => $userData->person->fechaNacimiento,
                    "ci" => $userData->person->ciExp(),
                    "email" => $userData->email,
                    "uid" => $userData->name,
                    "phone" => (count($userData->person->phones) > 0) ? $userData->person->phones[0]->numeroTelefono : null,
                    "sex" => $userData->person->sexo,
                    "type" => $userData->tipoUsuario_id,
                    "ico" => $userData->icoType
                ],
                'message' => 'Se completo correctamente',
            ], 200);
        }
        return response()->json([
            'success' => false,
            'message' => 'Credenciales insuficientes.',
        ], 401);
    }

    public function getHistoryCancelled($uid)
    {
        $user = Auth::user();
        if ($user && $user->tipoUsuario_id === 1)
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
                        $histories = $reading->historyAll()->orderBy('idHistorialCancelaciones')->orderBy('fechaHoraHCancelacion')->get();
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
                                ->first();
                            $configCancellation = ConfiguracionCancelacion::activeConfiguration()->descuentoCobroAgua;
                            $discountArray = explode("=>", $configCancellation);
                            $initialDateDiscount = Carbon::parse($discountArray[1]);
                            $finalDateDiscount = Carbon::parse($discountArray[2]);
                            $currentReading = Carbon::parse($reading->fechaMedicion);
                            if (($currentReading>= $initialDateDiscount) && ($currentReading <= $finalDateDiscount) && ($history->precioUnidad === 0.0) && ($history->diferenciaMedida === 0) && !$index_to_cancellation)
                            {
                                $history->delete();
                            }
                            else
                            {

                                $history_and_cancellation = [
                                    'idHistorialCancelaciones' => $history->idHistorialCancelaciones,
                                    'lectura_id' => $history->lectura_id,
                                    'cancelacion_id' => $history->cancelacion_id,
                                    'diferenciaMedida' => $history->diferenciaMedida,
                                    'precioUnidad' => $history->precioUnidad,
                                    'subTotal' => ($history->subTotal < 10) ? 10 : $history->subTotal,
                                    'montoCancelado' => $history->montoCancelado,
                                    'fechaHoraHCancelacion' => $history->fechaHoraHCancelacion,
                                    'estadoMedicion' => $history->estadoMedicion,
                                    'data_cancellation' => $index_to_cancellation
                                ];
                                $arrayToHistories[] = $history_and_cancellation;
                            }
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

    public function getPartnersExcept($uid)
    {
        $user = Auth::user();
        if ($user && $user->tipoUsuario_id === 1)
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
            $userExceptions = [];
            $userList = User::uidExcept($uid)->notAdmin()->with('person')->get();
            foreach ($userList as $user)
            {
                $userExceptions [] = [
                    'uid' => $user->name,
                    'fullName' => $user->person->fullName(),
                    'ci' => $user->person->ciExp()
                ];
            }
            return response()->json([
                'success' => false,
                'message' => 'Lista de usuarios exceptuando el seleccionado.',
                'data' => $userExceptions
            ], 200);
        }
        return response()->json([
            'success' => false,
            'message' => 'Credenciales insuficientes.',
        ], 401);
    }

    public function meterTransfer(Request $request)
    {
        $user = Auth::user();
        if ($user && $user->tipoUsuario_id === 1)
        {
            $validator = Validator::make(
                User::inputRulesMeterTransfer($request->input('newPartner'), $request->input('gauge'), $request->input('data')),
                User::rulesMeterTransfer()
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
            if ($request->input('newPartner'))
            {
                $validator = Validator::make(
                    Persona::inputRulesUpdate(
                        $request->input('data')['tipo'],
                        $request->input('data')['nombres'],
                        $request->input('data')['apellidos'],
                        explode(' ', $request->input('data')['ci'])[0],
                        $request->input('data')['fechaNacimiento'],
                        $request->input('data')['sexo'],
                        $request->input('data')['email'],
                        $request->input('data')['ico'],
                        $request->input('data')['telefonos']
                    ),
                    Persona::rulesUpdateTransfer()
                );
            }
            else
            {
                $validator = Validator::make(
                    Persona::inputRulesOtherPartner($request->input('data')['uid']),
                    Persona::rulesOtherPartner()
                );
            }
            $errors = $validator->errors();
            if ($validator->fails())
            {
                return response()->json([
                    'success' => false,
                    'message' => 'Formato incorrecto.',
                    'errors' => $errors->messages(),
                ], 400);
            }
            $gaugeCurrent = Medidor::numberGauge($request->input('gauge'))->first();
            $userWithNewMeter = null;
            if ($request->input('newPartner'))
            {
                $newPerson = new Persona();
                $newPerson->preparingSaving(
                    $request->input('data')['nombres'],
                    $request->input('data')['apellidos'],
                    $request->input('data')['ci'],
                    $request->input('data')['fechaNacimiento'],
                    $request->input('data')['sexo']
                );
                $newPersonGet = Persona::getData(explode(' ', $request->input('data')['ci'])[0]);
                $newUser = new User();
                $newUser->preparingSaving(
                    $request->input('data')['tipo'],
                    $newPersonGet,
                    $request->input('data')['email'],
                    $request->input('data')['ico']
                );
                if ($newPersonGet)
                {
                    Telefono::_instanceAndSaving($newPersonGet->idPersona, $request->input('data')['telefonos']);
                }
                $userWithNewMeter = User::uid($newUser->name)->first();
            }
            else
            {
                $userWithNewMeter = User::uid($request->input('data')['uid'])->first();
            }
            if ($gaugeCurrent && $userWithNewMeter)
            {
                try
                {
                    $idUserPrevious = $gaugeCurrent->usuario_id;
                    $gaugeCurrent->usuario_id = $userWithNewMeter->idUsuario;
                    $gaugeCurrent->save();
                    $key = Uuid::generate()->string;
                    Cancelacion::_instanceAndSaving($request->input('mount'), $key, 'BOLIVIANOS', 'EFECTIVO');
                    $cancellation = Cancelacion::getIDCancellation($key);
                    if ($cancellation)
                    {
                        HistorialTransferencia::_instanceAndSaving($userWithNewMeter->idUsuario, $gaugeCurrent->idMedidor, $cancellation->idCancelacion, $request->input('mount'), $idUserPrevious);
                        return response()->json([
                            'success' => true,
                            'message' => 'Transferencia completada exitosamente.'
                        ], 200);
                    }
                    return response()->json([
                        'success' => false,
                        'message' => 'Error al obtener la cancelación.'
                    ], 400);
                }
                catch (\Exception $e)
                {
                    return response()->json([
                        'success' => false,
                        'message' => 'No se pudo completar la transferencia.',
                        "error" => $e
                    ], 400);
                }
            }
            return response()->json([
                'success' => false,
                'message' => 'Error al cotejar el medidor con el socio.',
            ], 400);
        }
        return response()->json([
            'success' => false,
            'message' => 'Credenciales insuficientes.',
        ], 401);
    }

}
