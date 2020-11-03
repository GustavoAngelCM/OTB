<?php

namespace App\Http\Controllers;

use App\ConfiguracionCancelacion;
use App\HistorialCancelacion;
use App\Medidor;
use App\User;
use App\Lecturas;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use function PHPUnit\Framework\isNull;

class LecturaController extends Controller
{
    public function getPreviousReading()
    {
        $user = Auth::user();
        if ($user->tipoUsuario_id === 1)
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
                            ->first()
                    ];
                    $medidorsResponse[] = $m_n;
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
                $userResponse[] = $user_n;
            }
            return $userResponse;
        }
        return response()->json([
            'success' => false,
            'message' => 'Credenciales insuficientes.',
        ], 401);
    }

    public function setCurrentReadings(Request $request)
    {
        $user = Auth::user();
        if ($user->tipoUsuario_id === 1)
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

                $responseConflict = Lecturas::join('medidors', 'lecturas.medidor_id', '=', 'medidors.idMedidor')
                    ->join('users', 'medidors.usuario_id', '=', 'users.idUsuario')
                    ->where('lecturas.idLectura', '=', (int) $valor['key_lectura'])
                    ->select(
                        'medidors.numeroMedidor',
                        'users.name'
                    )
                    ->get()
                    ->first();
                if ($responseConflict->exists && ($responseConflict->numeroMedidor === $valor['numero']) &&
                    ($responseConflict->name === $valor['UID'])) {
                        $error = false;
                    }
                else
                {
                    $error = true;
                    break;
                }
            }
            if ($error === true)
            {
                return response()->json([
                    'success' => false,
                    'message' => 'Formato incorrecto.',
                    'errors' => $validator->errors()->messages(),
                ], 400);
            }

            $prevReading =  Lecturas::select('fechaMedicion')->orderBy('fechaMedicion', 'DESC')->get()->first();
            $date_current = Carbon::parse($prevReading->fechaMedicion);
            $date_current->setMonth($date_current->month + 1);

            $reading_for_month_exists = Lecturas::whereYear('fechaMedicion', $date_current->year)->whereMonth('fechaMedicion', $date_current->month)->get()->first();

            if ($reading_for_month_exists)
            {
                return response()->json([
                    'success' => false,
                    'message' => 'La lectura del mes anterior, ya ha sido registrada.',
                ], 400);
            }

            $limitDate = now();
            if ($date_current->year === $limitDate->year && $date_current->month <= $limitDate->month)
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
                    $newHistorialCancelacions->fechaHoraHCancelacion = now();
                    $newHistorialCancelacions->save();

                    $count_month_pending = HistorialCancelacion::select('historial_cancelacions.idHistorialCancelaciones', 'lecturas.fechaMedicion', 'lecturas.medida')
                        ->join('lecturas', 'historial_cancelacions.lectura_id', '=', 'lecturas.idLectura')
                        ->join('medidors', 'lecturas.medidor_id', '=', 'medidors.idMedidor')
                        ->where('lecturas.medidor_id', '=',  $data_lectura->medidor_id)
                        ->where('historial_cancelacions.estadoMedicion', '=', 'PENDING')
                        ->where('historial_cancelacions.diferenciaMedida', '!=', 0)
                        ->where('historial_cancelacions.precioUnidad', '!=', 0)
                        ->where('medidors.estado', '=', 'ACTIVO')
                        ->orderBy('fechaHoraHCancelacion', 'desc')
                        ->get();

                    if(count($count_month_pending) % (int) $config_cancelations->cantidadMesesParaMulta === 0) {
                        $newHistorialCancelacionsMulta = new HistorialCancelacion();
                        $newHistorialCancelacionsMulta->lectura_id = $lectura_for_history->ID;
                        $newHistorialCancelacionsMulta->cancelacion_id = null;
                        $newHistorialCancelacionsMulta->diferenciaMedida = 0;
                        $newHistorialCancelacionsMulta->precioUnidad = 0;
                        $newHistorialCancelacionsMulta->subTotal = $config_cancelations->montoMultaConsumoAgua;
                        $newHistorialCancelacionsMulta->fechaHoraHCancelacion = now();
                        $newHistorialCancelacionsMulta->save();
                    }
                }
                return response()->json([
                    'success' => true,
                    'message' => 'Lectura registrada correctamente.',
                ], 201);
            }

            return response()->json([
                'success' => true,
                'message' => 'No se puede realizar el registro del mes siguiente',
            ], 400);
        }

        return response()->json([
            'success' => false,
            'message' => 'Credenciales insuficientes.',
        ], 401);
    }

    public function updateReading(Request $request, $id)
    {
        $user = Auth::user();
        $id = (int) $id;
        if ($user->tipoUsuario_id === 1)
        {
            $validator = Validator::make(
                [
                    "medidaPrev" => $request->input('lastReading'),
                    "medida" => $request->input('currentReading'),
                    "idLectura" => $id
                ],
                [
                    "medidaPrev" => "bail|required|numeric",
                    "medida" => "bail|required|numeric",
                    "idLectura" => "bail|required|numeric|exists:lecturas",
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
            $readingPrevious = Lecturas::reading($id)->first();
            if ($request->input('lastReading') === $readingPrevious->medida)
            {
                $historyCancellation = HistorialCancelacion::pendingValid($id);
                $recalculateDifference = $request->input('currentReading') - ($readingPrevious->medida - $historyCancellation->diferenciaMedida);
                $historyCancellation->diferenciaMedida = $recalculateDifference;
                $historyCancellation->subTotal = $recalculateDifference * $historyCancellation->precioUnidad;
                $historyCancellation->save();
                $readingPrevious->medida = $request->input('currentReading');
                $readingPrevious->save();
                return response()->json([
                    'success' => true,
                    'message' => "Registro actualizado correctamente",
                ], 200);
            }
            return response()->json([
                'success' => false,
                'message' => 'Error al verificar lectura',
            ], 400);
        }
        return response()->json([
            'success' => false,
            'message' => 'Credenciales insuficientes.',
        ], 401);
    }

    public function monthReading(Request $request)
    {
        $user = Auth::user();
        if ($user->tipoUsuario_id === 1)
        {
            $validator = Validator::make(
                [
                    "ordenMedidor" => $request->input('medidor'),
                    "fechaMedicion" => $request->input('mes')
                ],
                [
                    "ordenMedidor" => "bail|required|exists:medidors",
                    "fechaMedicion" => "bail|required|date"
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
            $monthSelected = Carbon::parse($request->input('mes'));
            $readingForGauge = Medidor::join('lecturas', 'medidors.idMedidor', '=', 'lecturas.medidor_id')
                ->where('medidors.ordenMedidor', $request->input('medidor'))
                ->whereYear('lecturas.fechaMedicion', $monthSelected->year)
                ->whereMonth('lecturas.fechaMedicion', $monthSelected->month)
                ->select(['lecturas.medida', 'lecturas.idLectura'])
                ->first();
            if ($readingForGauge->exists())
            {
                $existCancellation = Lecturas::searchCancellationsToReading($readingForGauge->idLectura);
                if (count($existCancellation) > 0)
                {
                    return response()->json([
                        'success' => false,
                        'message' => 'Esta lectura ya ha sido pagada.',
                    ], 400);
                }
                return response()->json([
                    'success' => true,
                    'data' => $readingForGauge
                ], 200);
            }
            return response()->json([
                'success' => false,
                'message' => 'Lectura inexistente.',
            ], 400);
        }
        return response()->json([
            'success' => false,
            'message' => 'Credenciales insuficientes.',
        ], 401);
    }

    public function monthsReadings()
    {
        $listMonthsReadings = Lecturas::selectRaw("DISTINCT (DATE_FORMAT(fechaMedicion, '%Y %m')) as date")
            ->groupBy('fechaMedicion')
            ->orderBy('fechaMedicion')
            ->get();
        $monthsResponse = [];
        Carbon::setLocale('es');
        foreach ($listMonthsReadings as $date)
        {
            $parserCarbon = Carbon::parse(strtr($date->date, [' ' => '-']).'-01');
            $monthsResponse [] = [
                'parserDate' => strtoupper("{$parserCarbon->monthName} de {$parserCarbon->year}"),
                'date' => $parserCarbon
            ];
        }
        return response()->json([
            'success' => true,
            'data' => array_reverse($monthsResponse)
        ], 200);
    }

}
