<?php

namespace App\Http\Controllers;

use App\ConfiguracionCancelacion;
use App\HistorialCancelacion;
use App\User;
use App\Lecturas;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LecturaController extends Controller
{
    public function getPreviousReading()
    {
        $user = Auth::user();
        if ($user->tipoUsuario_id == 1)
        {
            $users = User::where('tipoUsuario_id', '!=', 1)->get();
            $userResponse = [];
            foreach ($users as $u)
            {
                $medidorsResponse = [];
                foreach ($u->gauges()->get() as $m)
                {
                    $m_n = [
                        'orden' => $m->ordenMedidor,
                        'numero' => $m->numeroMedidor,
                        'direccion' => $m->direccion,
                        'fechaInstalacion' => $m->fechaInstalacion,
                        'estado' => $m->estado,
                        'lectura' => $m->readings()
                            ->select(
                                'idLectura as key_lectura',
                                'medida',
                                'fechaMedicion',
                                'estado'
                            )
//                            ->whereYear('fechaMedicion', date('Y'))
//                            ->whereMonth('fechaMedicion', date('m'))
                            ->orderByDesc('fechaMedicion')
                            ->first()
                    ];
                    array_push($medidorsResponse, $m_n);
                }
                $user_n = (object) [
                    'tipo_usuario' => $u->tipo()->select(
                            'nombreTipoUsuario as nombreTipo'
                        )->get()->first(),
                    'user' => $u->name,
                    'email' => $u->email,
                    'ico' => $u->icoType,
                    'persona' => $u->person()->select(
                            DB::raw('concat_ws(" ", pNombre, sNombre, apellidoP, apellidoM) as fullName'),
                            DB::raw('concat_ws(" ", pNombre, apellidoP) as shortName'),
                            'ci',
                            'expxedicion',
                            'fechaNacimiento',
                            'sexo'
                        )->get()->first(),
                    'medidores' => $medidorsResponse
                ];
                array_push($userResponse, $user_n);
            }
            return $userResponse;
        }
        else
        {
            return response()->json([
                'success' => false,
                'message' => 'Credenciales insuficientes.',
            ], 401);
        }
    }

    public function setCurrentReadings(Request $request)
    {
        $user = Auth::user();
        if ($user->tipoUsuario_id == 1)
        {
            $validator = null;
            $error = false;
            foreach ($request->json() as $clave => $valor)
            {
                $validator = Validator::make(
                    [
                        "name" => $valor['UID'],
                        "ci" => $valor['key'],
                        "idLectura" => $valor['key_lectura'],
                        "ordenMedidor" => $valor['orden'],
                        "numeroMedidor" => $valor['numero'],
                        "medida" => $valor['medida'],
                        "fechaMedicion" => $valor['fecha_lectura_registro'],
                        "estado" => $valor['estado_medidor'],
                        "estado_mc" => $valor['estado_medicion'],
                        "medida_prev" => $valor['medida_prev'],
                        "fechaMedicion_prev" => $valor['fecha_lectura_anterior'],
                    ],
                    [
                        "name" => "bail|required|exists:users",
                        "ci" => 'bail|required|exists:personas',
                        "idLectura" => 'bail|required',
                        "ordenMedidor" => 'bail|required|exists:medidors',
                        "numeroMedidor" => 'bail|required|exists:medidors',
                        "medida" => 'bail|required',
                        "fechaMedicion" => 'bail|required',
                        "estado" => 'bail|required',
                        "estado_mc" => 'bail|required',
                        "medida_prev" => 'bail|required',
                        "fechaMedicion_prev" => 'bail|required',
                    ]
                );
                if ($validator->fails())
                {
                    $error = true;
                    break;
                }
                else
                {
                    $responseConflict = Lecturas::join('medidors', 'lecturas.medidor_id', '=', 'medidors.idMedidor')
                        ->join('users', 'medidors.usuario_id', '=', 'users.idUsuario')
                        ->where('lecturas.idLectura', '=', (int) $valor['key_lectura'])
                        ->select(
                            'medidors.numeroMedidor',
                            'users.name'
                        )
                        ->get()
                        ->first();
                    if ($responseConflict->exists)
                    {
                        if (
                            ($responseConflict->numeroMedidor == $valor['numero']) &&
                            ($responseConflict->name == $valor['UID'])
                        )
                        {
                            $error = false;
                        }
                        else
                        {
                            $error = true;
                            break;
                        }
                    }
                    else
                    {
                        $error = true;
                        break;
                    }
                }
            }
            if ($error == true)
            {
                return response()->json([
                    'success' => false,
                    'message' => 'Formato incorrecto.',
                    'errors' => $validator->errors()->messages(),
                ], 400);
            }
            else
            {
                $date_current = now();
                $date_current->setMonth($date_current->month - 1);

                $reading_for_month_exists = Lecturas::whereYear('fechaMedicion', $date_current->year)
                    ->whereMonth('fechaMedicion', $date_current->month)
                    ->whereDay('fechaMedicion', $date_current->day)
                    ->get()
                    ->first();

                if ($reading_for_month_exists)
                {
                    return response()->json([
                        'success' => false,
                        'message' => 'La lectura del mes anterior, ya ha sido registrada.',
                    ], 400);
                }
                else
                {
                    foreach ($request->json() as $clave => $valor)
                    {
                        $data_lectura = Lecturas::join('medidors', 'lecturas.medidor_id', '=', 'medidors.idMedidor')
                            ->join('users', 'medidors.usuario_id', '=', 'users.idUsuario')
                            ->where('lecturas.idLectura', '=', (int) $valor['key_lectura'])
                            ->select(
                                'lecturas.medidor_id',
                                'lecturas.medida',
                                'users.name'
                            )
                            ->get()
                            ->first();

                        $newLectura = new Lecturas();
                        $newLectura->medidor_id = $data_lectura->medidor_id;
                        $newLectura->usuario_id = $user->idUsuario;
                        $newLectura->medida = $valor['medida'];
                        $newLectura->fechaMedicion = $date_current;
                        $newLectura->estado = 'NORMAL';
                        $newLectura->save();

                        $lectura_for_history = Lecturas::select('idLectura as ID')
                            ->where('medidor_id', '=', $data_lectura->medidor_id)
                            ->whereYear('fechaMedicion', $date_current->year)
                            ->whereMonth('fechaMedicion', $date_current->month)
                            ->whereDay('fechaMedicion', $date_current->day)
                            ->orderBy('idLectura', 'desc')
                            ->get()
                            ->first();

                        $config_cancelations = ConfiguracionCancelacion::where('activo', '=', true)->get()->first();

                        $newHistorialCancelacions = new HistorialCancelacion();
                        $newHistorialCancelacions->lectura_id = $lectura_for_history->ID;
                        $newHistorialCancelacions->cancelacion_id = null;
                        $newHistorialCancelacions->diferenciaMedida = (int) $valor['medida'] - (int) $data_lectura->medida;
                        $newHistorialCancelacions->precioUnidad = $config_cancelations->montoCuboAgua;
                        $newHistorialCancelacions->subTotal = $newHistorialCancelacions->diferenciaMedida * $newHistorialCancelacions->precioUnidad;
                        $newHistorialCancelacions->save();

                        $count_month_pending = HistorialCancelacion::select('historial_cancelacions.idHistorialCancelaciones', 'lecturas.fechaMedicion', 'lecturas.medida')
                            ->join('lecturas', 'historial_cancelacions.lectura_id', '=', 'lecturas.idLectura')
                            ->join('medidors', 'lecturas.medidor_id', '=', 'medidors.idMedidor')
                            ->where('lecturas.medidor_id', '=',  $data_lectura->medidor_id)
                            ->where('historial_cancelacions.estadoMedicion', '=', 'PENDING')
                            ->where('medidors.estado', '=', 'ACTIVO')
                            ->orderBy('fechaHoraHCancelacion', 'desc')
                            ->get();

                        if(count($count_month_pending) % (int) $config_cancelations->cantidadMesesParaMulta == 0) {
                            $newHistorialCancelacionsMulta = new HistorialCancelacion();
                            $newHistorialCancelacionsMulta->lectura_id = $lectura_for_history->ID;
                            $newHistorialCancelacionsMulta->cancelacion_id = null;
                            $newHistorialCancelacionsMulta->diferenciaMedida = 0;
                            $newHistorialCancelacionsMulta->precioUnidad = 0;
                            $newHistorialCancelacionsMulta->subTotal = $config_cancelations->montoMultaConsumoAgua;
                            $newHistorialCancelacionsMulta->save();
                        }
                    }
                    return response()->json([
                        'success' => true,
                        'message' => 'Lectura registrada correctamente.',
                    ], 201);
                }
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Credenciales insuficientes.',
            ], 401);
        }
    }

}
