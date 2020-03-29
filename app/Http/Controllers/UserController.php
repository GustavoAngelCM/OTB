<?php

namespace App\Http\Controllers;

use App\Medidor;
use App\Persona;
use App\Telefono;
use App\User;
use App\Lecturas;
use App\Cancelacion;
use App\HistorialTransferencia;
use Webpatser\Uuid\Uuid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    //create
    public function create(Request $request)
    {
        $transaction_keys = array();
        $validator = Validator::make(
            [
                'idTipoUsuario' => $request->input('tipo'),
                'nombres' => $request->input('nombres'),
                'apellidos' => $request->input('apellidos'),
                'ci' => explode(' ', $request->input('ci'))[0],
                'fechaNacimiento' => $request->input('fechaNacimiento'),
                'sexo' => $request->input('sexo'),
                'email' => $request->input('email'),
                'ico' => $request->input('ico'),
                'medidores' => $request->input('medidores'),
                'telefonos' => $request->input('telefonos'),
            ],
            [
                'idTipoUsuario' => 'bail|required|numeric',
                'nombres' => 'required',
                'apellidos' => 'required',
                'ci' => 'bail|required|max:15|unique:personas',
                'fechaNacimiento' => 'bail|required|date',
                'sexo' => 'bail|required|max:1',
                'email' => 'bail|required|email|max:50|unique:users',
                'ico' => 'required',
                'medidores' => 'required',
                'telefonos' => 'required'
            ]
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
                    [
                        'ordenMedidor' => $valor['orden'],
                        'numeroMedidor' => $valor['numero'],
                        'direccion' => $valor['direccion'],
                        'fechaInstalacion' => $valor['fechaInstalacion'],
                        'estado' => $valor['estado'],
                        'medida' => $valor['lectura'],
                        'compra' => $valor['compra'],
                    ],
                    [
                        'ordenMedidor' => 'bail|required|unique:medidors',
                        'numeroMedidor' => 'bail|required|unique:medidors',
                        'direccion' => 'bail|required|max:150',
                        'fechaInstalacion' => 'bail|required|date',
                        'estado' => 'bail|nullable|boolean',
                        'medida' => 'bail|required|numeric',
                        'compra' => 'required',
                    ]
                );
                if ($validator->fails()) {
                    $error = true;
                    break;
                } else {
                    $validator = null;
                    $error = false;

                    $validator = Validator::make(
                        [
                            'montoCancelacion' => $valor['compra']['precio'],
                            'moneda' => $valor['compra']['moneda'],
                            'tipoCancelacion' => $valor['compra']['tipo'],
                            'keyCancelacion' => $key,
                        ],
                        [
                            'montoCancelacion' => 'bail|required|numeric',
                            'moneda' => 'bail|required',
                            'tipoCancelacion' => 'bail|required',
                            'keyCancelacion' => 'bail|required|unique:cancelacions'
                        ]
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
                        [
                            'numeroTelefono' => $valor,
                        ],
                        [
                            'numeroTelefono' => 'bail|required|unique:telefonos',
                        ]
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
//                    dd(Uuid::generate()->string);
                    $personaRequest = new Persona();
                    $personaRequest->seterNames($request->input('nombres'));
                    $personaRequest->seterLastNames($request->input('apellidos'));
                    $personaRequest->seterCIEXP($request->input('ci'));
                    $personaRequest->fechaNacimiento = $request->input('fechaNacimiento');
                    $personaRequest->sexo = $request->input('sexo');
                    $personaRequest->save();

                    $personGET = Persona::where('ci', '=', $personaRequest->ci)->first();

                    foreach ($request->input('telefonos') as $valor) {
                        $telefonoRequest = new Telefono();
                        $telefonoRequest->persona_id = $personGET->idPersona;
                        $telefonoRequest->numeroTelefono = $valor;
                        $telefonoRequest->save();
                    }

                    $usuarioRequest = new User();
                    $usuarioRequest->tipoUsuario_id = $request->input('tipo');
                    $usuarioRequest->persona_id = $personGET->idPersona;
                    $usuarioRequest->name = strtolower("{$personaRequest->pNombre[0]}{$personaRequest->pNombre[1]}{$personaRequest->pNombre[2]}{$personaRequest->ci[0]}{$personaRequest->ci[1]}{$personaRequest->ci[2]}{$personaRequest->apellidoP}");
                    $usuarioRequest->email = $request->input('email');
                    $usuarioRequest->password = Hash::make($personaRequest->ci);
                    $usuarioRequest->icoType = $request->input('ico');
                    $usuarioRequest->save();

                    $userGET = User::where('persona_id', '=', $personGET->idPersona)->first();

                    $c_key = 0;
                    foreach ($request->input('medidores') as $clave => $valor) {
                        $medidoresRequest =  new Medidor();
                        $medidoresRequest->usuario_id = $userGET->idUsuario;
                        $medidoresRequest->ordenMedidor = $valor['orden'];
                        $medidoresRequest->numeroMedidor = $valor['numero'];
                        $medidoresRequest->direccion = $valor['direccion'];
                        $medidoresRequest->fechaInstalacion = $valor['fechaInstalacion'];
                        $medidoresRequest->estado = ($valor['estado'] == null) ? true : $valor['estado'];
                        $medidoresRequest->save();

                        $medidorGET = Medidor::where('numeroMedidor', '=', $valor['numero'])->first();

                        $lecturaRequest = new Lecturas();
                        $lecturaRequest->medidor_id = $medidorGET->idMedidor;
                        $lecturaRequest->usuario_id = $userGET->idUsuario;
                        $lecturaRequest->medida = $valor['lectura'];
                        $lecturaRequest->save();

                        $cancelacionRequest = new Cancelacion();
                        $cancelacionRequest->montoCancelacion = $valor['compra']['precio'];
                        $cancelacionRequest->keyCancelacion = $transaction_keys[$c_key];
                        $cancelacionRequest->moneda = strtoupper($valor['compra']['moneda']);
                        $cancelacionRequest->tipoCancelacion = strtoupper($valor['compra']['tipo']);
                        $cancelacionRequest->save();

                        $cancelacion_GET = Cancelacion::where('keyCancelacion', $transaction_keys[$c_key])->get()->first();

                        $transferenciaRequest = new HistorialTransferencia();
                        $transferenciaRequest->usuario_anterior_id = null;
                        $transferenciaRequest->usuario_siguiente_id = $userGET->idUsuario;
                        $transferenciaRequest->cancelacion_id = $cancelacion_GET->idCancelacion;
                        $transferenciaRequest->montoTotalTransferencia = (isset($valor['compra']['saldo'])) ? $valor['compra']['saldo'] + $valor['compra']['precio'] : $valor['compra']['precio'];
                        $transferenciaRequest->montoCancelado = $valor['compra']['precio'];
                        $transferenciaRequest->estadoTransferencia = (isset($valor['compra']['saldo'])) ? 'IN_PROCESS' : 'COMPLETED';
                        $transferenciaRequest->save();
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
}
