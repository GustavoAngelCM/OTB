<?php

namespace App\Http\Controllers;

use App\Medidor;
use App\Persona;
use App\Telefono;
use App\User;
use App\Lecturas;
use App\Cancelacion;
use App\HistorialTransferencia;
use Carbon\Carbon;
use Faker\Factory;
use Illuminate\Support\Facades\Auth;
use Webpatser\Uuid\Uuid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    //create
    public function create(Request $request)
    {
        $transaction_keys = array();
        $validator = Validator::make(
            User::inputRulesUser(
                $request->input('tipo'),
                $request->input('nombres'),
                $request->input('apellidos'),
                explode(' ', $request->input('ci'))[0],
                $request->input('fechaNacimiento'),
                $request->input('sexo'),
                $request->input('email'),
                $request->input('ico'),
                $request->input('medidores'),
                $request->input('telefonos')
            ),
            User::rulesUser( )
        );
        $errors = $validator->errors();
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Formato incorrecto.',
                'errors' => $errors->messages(),
            ], 400);
        }
        $validator = null;
        $error = false;
        foreach ($request->input('medidores') as $clave => $valor) {
            try {
                $key = Uuid::generate()->string;
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al registrar.',
                    'errors' => 'token',
                ], 400);
            }
            $transaction_keys[] = $key;
            $validator = Validator::make(
                Medidor::inputRulesGauges($valor),
                Medidor::rulesGauges()
            );
            if ($validator->fails()) {
                $error = true;
                break;
            }

            $validator = null;
            $error = false;

            $validator = Validator::make(
                Cancelacion::inputRulesGaugeTransaction($valor['compra'], $key),
                Cancelacion::rulesGaugeTransaction()
            );
            if ($validator->fails()) {
                $error = true;
                break;
            }
        }
        if ($error === true) {
            return response()->json([
                'success' => false,
                'message' => 'Formato incorrecto.',
                'errors' => $validator->errors()->messages(),
            ], 400);
        }

        foreach ($request->input('telefonos') as $valor) {
            $validator = Validator::make(
                Telefono::inputRulesPhone($valor),
                Telefono::rulesPhone()
            );
            if ($validator->fails()) {
                $error = true;
                break;
            }
        }
        if ($error === true) {
            return response()->json([
                'success' => false,
                'message' => 'Formato incorrecto.',
                'errors' => $validator->errors()->messages(),
            ], 400);
        }

        $personaRequest = new Persona();
        $personaRequest->preparingSaving($request->input('nombres'), $request->input('apellidos'), $request->input('ci'), $request->input('fechaNacimiento'), $request->input('sexo'));

        $personGET = Persona::getData($personaRequest->ci);

        foreach ($request->input('telefonos') as $valor) {
            $telefonoRequest = new Telefono();
            $telefonoRequest->preparingSaving($personGET->idPersona, $valor);
        }

        $usuarioRequest = new User();
        $usuarioRequest->preparingSaving($request->input('tipo'), $personGET, $request->input('email'), $request->input('ico'));

        $userGET = User::getDataUsingCI($personGET->idPersona);

        $c_key = 0;
        foreach ($request->input('medidores') as $clave => $valor)
        {
            $medidoresRequest =  new Medidor();
            $medidoresRequest->preparingSaving($userGET->idUsuario, $valor['numero'], $valor['direccion'], $valor['fechaInstalacion'], $valor['estado']);

            $medidorGET = Medidor::getDataUsingNumber($valor['numero']);

            $user_auth = Auth::user();

            $lecturaRequest = new Lecturas();
            $lecturaRequest->preparingSaving($medidorGET->idMedidor, $user_auth->idUsuario, $valor['lectura'], ($valor['fechaNivelacion'] ?? null));

            $ultimateRegisterReading = Lecturas::select("fechaMedicion")->groupBy('fechaMedicion')->orderBy('fechaMedicion', 'desc')->first();
            $dateLimit = Carbon::parse($ultimateRegisterReading->fechaMedicion);
            $dateLimit->setDay(1);
            $dateFlag = Carbon::parse($valor['fechaNivelacion']);
            $dateFlag->setDay(1);
            $gaugeReading = $valor['lectura'];
            while ($dateLimit > $dateFlag)
            {
                $gaugeReading += 10;
                $dateFlag->setMonth($dateFlag->month + 1);
                $lecturaRequest = new Lecturas();
                $lecturaRequest->preparingSaving($medidorGET->idMedidor, $user_auth->idUsuario, $gaugeReading, $dateFlag);
            }

            $cancelacionRequest = new Cancelacion();
            $cancelacionRequest->prepareSaving($valor['compra']['precio'], $transaction_keys[$c_key], $valor['compra']['moneda'], $valor['compra']['tipo']);

            $cancelacion_GET = Cancelacion::getIDCancellation($transaction_keys[$c_key]);

            $transferenciaRequest = new HistorialTransferencia();
            $transferenciaRequest->preparingSaving($userGET->idUsuario, $medidorGET->idMedidor, $cancelacion_GET->idCancelacion, $valor['compra']['precio']);
            $c_key ++;
        }

        return response()->json([
            'success' => true,
            'message' => 'Registro creado exitosamente.',
            'errors' => null,
        ], 201);
    }

    public function managers(): array
    {
        $usersManagers = User::getUsersManagers();
        $managers = [];
        foreach ($usersManagers as $userManager) {
            $managers[] = [
                "fullName" => (string)($userManager->person->fullName()),
                "shortName" => (string)($userManager->person->shortName()),
                "ico" => $userManager->icoType
            ];
        }
        return $managers;
    }

    public function updatePass($uid)
    {
        $user = Auth::user();
        if ($user->tipoUsuario_id === 1)
        {
            $faker = Factory::create();
            $secret =$faker->password;
            $userSelected = User::uid($uid)->first();
            $userSelected->password = Hash::make($secret);
            $userSelected->save();
            return response()->json([
                'success' => true,
                'secret' => $secret,
            ], 200);
        }
        return response()->json([
            'success' => false,
            'message' => 'Credenciales insuficientes.',
        ], 401);
    }

}
