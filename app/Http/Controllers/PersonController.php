<?php

namespace App\Http\Controllers;

use App\Cancelacion;
use App\Lecturas;
use App\Persona;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PersonController extends Controller
{
    public function modifyingData(Request $request, $id)
    {
        $user = Auth::user();
        if ($user->tipoUsuario_id === 1)
        {
            $id = (int) $id;
            $validator = Validator::make(
                [
                    'idPersona' => $id,
                    'nombres' => $request->input('nombres'),
                    'apellidos' => $request->input('apellidos'),
                    'ci'=>  explode(' ', $request->input('ci'))[0],
                    'sexo'=> $request->input('sexo'),
                    'fechaNacimiento'=> $request->input('fechaNacimiento')
                ],
                [
                    'idPersona' => 'bail|required|integer|exists:personas',
                    'nombres' => 'string|nullable',
                    'apellidos' => 'string|nullable',
                    'ci' => 'bail|max:15|exists:personas',
                    'fechaNacimiento' => 'date|nullable',
                    'sexo' => 'max:1',
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

            try
            {
                Persona::updatingFields($id, $request->input());
                return response()->json([
                    'success' => true,
                    'message' => 'Registro modificado exitosamente.',
                    'errors' => null,
                ], 200);
            }
            catch (\Exception $e)
            {
                return response()->json([
                    'success' => false,
                    'message' => 'No se ha podido realizar la solicitud',
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

    public function deleteData($id)
    {
        $user = Auth::user();
        if ($user->tipoUsuario_id === 1)
        {
            $id = (int) $id;
            $validator = Validator::make(
                [
                    'idPersona' => $id
                ],
                [
                    'idPersona' => 'bail|required|integer|exists:personas'
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

            if (!Persona::existsRelationPersonGauges($id))
            {
                Persona::where('idPersona',$id)->delete();
                return response()->json([
                    'success' => true,
                    'message' => 'Registro eliminado exitosamente.',
                    'errors' => null,
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'Dependia existente, imposible eliminar.',
            ], 400);
        }
        return response()->json([
            'success' => false,
            'message' => 'Credenciales insuficientes.',
        ], 401);
    }

    public function gaugesHistoryCancellation($uid): array
    {
        $user = User::select('persona_id')->where('name', $uid)->get()->first();
        $userHistory = Persona::where('idPersona', $user->persona_id)->with(['user.gauges.readings.historyAll.cancellation', 'phones', 'user.gauges.assists.event'])->get()->first();
        $phones = [];
        foreach ($userHistory->phones as $phone) {
            $phones[] = $phone->numeroTelefono;
        }
        $gauges = (object)[];
        foreach ($userHistory->user->gauges as $gauge) {
            $readings = [];
            $assemblies = [];
            foreach ($gauge->readings as $reading) {
                $mountFine = 0;
                $i = 0;
                foreach ($reading->historyAll as $history) {
                    if ($history->estadoMedicion === 'CANCELLED') {
                        $readings[] = [
                            "date" => $reading->fechaMedicion,
                            "mount" => $history->subTotal,
                            "state" => $history->estadoMedicion,
                            "reading" => $reading->medida,
                            "sale" => $history->cancellation->montoCancelacion . ' ' . Cancelacion::currencyAbbreviation($history->cancellation->moneda),
                            "keyTransaction" => $history->cancellation->keyCancelacion
                        ];
                        $mountFine = 0;
                    }
                    if ($history->cancelacion_id === null) {
                        $mountFine = ($i === 0) ? $history->subTotal : "{$mountFine} + <span style='color: red'>{$history->subTotal}</span>";
                        $i++;
                    }
                }
                $historyUltimate = $reading->historyAll[count($reading->historyAll) - 1];
                $keysCancellations = Lecturas::searchCancellationsToReading($reading->idLectura);
                $keysCancellationsMounts = Lecturas::searchCancellationsToReading($reading->idLectura, true);
                $_keysCancellations = null;
                $_mounts = '';
                if (count($keysCancellations))
                {
                    $i = 0;
                    foreach ($keysCancellations as $keyCancellation) {
                        $_keysCancellations = ($i === 0) ? $keyCancellation->keyCancelacion : "{$_keysCancellations} | {$keyCancellation->keyCancelacion}";
                        $i++;
                    }
                    $i = 0;
                    foreach ($keysCancellationsMounts as $keyCancellationMount) {
                        if ($keyCancellationMount->montoCancelado > 0) {
                            $_mounts = ($i === 0) ? $keyCancellationMount->montoCancelado . ' ' . Cancelacion::currencyAbbreviation($keyCancellationMount->moneda) : $_mounts . ' + ' . $keyCancellationMount->montoCancelado . ' ' . Cancelacion::currencyAbbreviation($keyCancellationMount->moneda);
                        }
                        $i++;
                    }
                }
                $readings[] = [
                    "date" => $reading->fechaMedicion,
                    "mount" => $mountFine,
                    "state" => $historyUltimate->estadoMedicion,
                    "reading" => $reading->medida,
                    "sale" => $_mounts,
                    "keyTransaction" => $_keysCancellations,
                ];
            }
            foreach ($gauge->assists as $assistance) {
                $assemblies[] = [
                    "date" => $assistance->event->fechaEvento,
                    "mount" => $assistance->event->montoMulta,
                    "assistance" => $assistance->asistio,
                    "event" => $assistance->event->nombreEvento,
                    "description" => $assistance->event->descripcionEvento,
                    "finalized" => $assistance->event->finalizado
                ];
            }
            $gauges->{"gauge{$gauge->ordenMedidor}"}= [
                "order" => $gauge->ordenMedidor,
                "number" => $gauge->numeroMedidor,
                "direction" => $gauge->direccion,
                "readings" => $readings,
                "assemblies" => $assemblies
            ];
        }
        return [
            "fullName" => $userHistory->fullName(),
            "shortName" => $userHistory->shortName(),
            "ci" => $userHistory->ciExp(),
            "email" => $userHistory->user->email,
            "ico" => $userHistory->user->icoType,
            "phones" => $phones,
            "gauges" => $gauges
        ];
    }

}
