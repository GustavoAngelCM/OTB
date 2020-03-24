<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

    }

}
