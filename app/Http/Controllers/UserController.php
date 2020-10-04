<?php

namespace App\Http\Controllers;

use App\Medidor;
use App\Persona;
use App\Telefono;
use App\User;
use App\Lecturas;
use App\Cancelacion;
use App\HistorialTransferencia;
use Illuminate\Support\Facades\Auth;
use Webpatser\Uuid\Uuid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
        } else {
            $validator = null;
            $error = false;
            foreach ($request->input('medidores') as $clave => $valor) {
                $key = Uuid::generate()->string;
                array_push($transaction_keys, $key);
                $validator = Validator::make(
                    Medidor::inputRulesGauges($valor),
                    Medidor::rulesGauges()
                );
                if ($validator->fails()) {
                    $error = true;
                    break;
                } else {
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
            }
            if ($error == true) {
                return response()->json([
                    'success' => false,
                    'message' => 'Formato incorrecto.',
                    'errors' => $validator->errors()->messages(),
                ], 400);
            } else {
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
                if ($error == true) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Formato incorrecto.',
                        'errors' => $validator->errors()->messages(),
                    ], 400);
                } else {
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
                    foreach ($request->input('medidores') as $clave => $valor) {
                        $medidoresRequest =  new Medidor();
                        $medidoresRequest->preparingSaving($userGET->idUsuario, $valor['orden'], $valor['numero'], $valor['direccion'], $valor['fechaInstalacion'], $valor['estado']);

                        $medidorGET = Medidor::getDataUsingNumber($valor['numero']);

                        $user_auth = Auth::user();

                        $lecturaRequest = new Lecturas();
                        $lecturaRequest->preparingSaving($medidorGET->idMedidor, $user_auth->idUsuario, $valor['lectura'], ((isset($valor['fechaMedicion'])) ? $valor['fechaMedicion'] : null ));

                        $cancelacionRequest = new Cancelacion();
                        $cancelacionRequest->preparingSaving($valor['compra']['precio'], $transaction_keys[$c_key], $valor['compra']['moneda'], $valor['compra']['tipo']);

                        $cancelacion_GET = Cancelacion::getIDCancellation($transaction_keys[$c_key]);

                        $transferenciaRequest = new HistorialTransferencia();
                        $transferenciaRequest->preparingSaving($userGET->idUsuario, $medidorGET->idMedidor, $cancelacion_GET->idCancelacion, $valor['compra']['saldo'], $valor['compra']['precio']);
                        $c_key ++;
                    }

                    return response()->json([
                        'success' => true,
                        'message' => 'Registro creado exitosamente.',
                        'errors' => null,
                    ], 201);
                }
            }
        }
    }

    public function managers()
    {
        $usersManagers = User::getUsersManagers();
        $managers = [];
        foreach ($usersManagers as $userManager) {
            array_push($managers, [
                "fullName" => "{$userManager->person->fullName()}",
                "shortName" => "{$userManager->person->shortName()}",
                "ico" => $userManager->icoType
            ]);
        }
        return $managers;
    }

}
