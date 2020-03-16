<?php

namespace App\Http\Controllers;

use App\Medidor;
use App\Persona;
use App\Telefono;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    //create
    public function create(Request $request)
    {
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
                'email' => 'bail|required|email|max:35|unique:users',
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
                $validator = Validator::make(
                    [
                        'ordenMedidor' => $valor['orden'],
                        'numeroMedidor' => $valor['numero'],
                        'direccion' => $valor['direccion'],
                        'fechaInstalacion' => $valor['fechaInstalacion'],
                        'estado' => $valor['estado'],
                    ],
                    [
                        'ordenMedidor' => 'bail|required|unique:medidors',
                        'numeroMedidor' => 'bail|required|numeric|unique:medidors',
                        'direccion' => 'bail|required|max:150',
                        'fechaInstalacion' => 'bail|required|date',
                        'estado' => 'bail|nullable|boolean'
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
                    $usuarioRequest->name = strtolower("{$personaRequest->pNombre[0]}{$personaRequest->pNombre[1]}{$personaRequest->pNombre[2]}{$personaRequest->apellidoM}");
                    $usuarioRequest->email = $request->input('email');
                    $usuarioRequest->password = Hash::make($personaRequest->ci);
                    $usuarioRequest->icoType = $request->input('ico');
                    $usuarioRequest->save();

                    $userGET = User::where('persona_id', '=', $personGET->idPersona)->first();

                    foreach ($request->input('medidores') as $clave => $valor) {
                        $medidoresRequest =  new Medidor();
                        $medidoresRequest->usuario_id = $userGET->idUsuario;
                        $medidoresRequest->ordenMedidor = $valor['orden'];
                        $medidoresRequest->numeroMedidor = $valor['numero'];
                        $medidoresRequest->direccion = $valor['direccion'];
                        $medidoresRequest->fechaInstalacion = $valor['fechaInstalacion'];
                        $medidoresRequest->estado = ($valor['estado'] == null) ? true : $valor['estado'];
                        $medidoresRequest->save();
                    }

                    return response()->json([
                        'success' => true,
                        'message' => 'Registro creado exitosamente.',
                        'errors' => null,
                    ], 201);
                }
            }
//            $validator = null;
        }
    }
}
