<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Medidor extends Model
{
    protected  $primaryKey = 'idMedidor';

    public function readings(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany('App\Lecturas', 'medidor_id', 'idMedidor')->orderBy('fechaMedicion', 'desc');
    }

    public function assists(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany('App\Asistencia', 'medidor_id', 'idMedidor')->orderBy('fechaHoraAsistencia', 'desc')->orderBy('idAsistencia', 'desc');
    }

    public static function inputRulesGauges($gauge): array
    {
        return [
            'ordenMedidor' => $gauge['orden'],
            'numeroMedidor' => $gauge['numero'],
            'direccion' => $gauge['direccion'],
            'fechaInstalacion' => $gauge['fechaInstalacion'],
            'estado' => $gauge['estado'],
            'medida' => $gauge['lectura'],
            'compra' => $gauge['compra']
        ];
    }

            public static function rulesGauges(): array
    {
        return [
            'ordenMedidor' => 'bail|required|unique:medidors',
            'numeroMedidor' => 'bail|required|unique:medidors',
            'direccion' => 'bail|required|max:150',
            'fechaInstalacion' => 'bail|required|date',
            'estado' => 'bail|nullable|boolean',
            'medida' => 'bail|required|numeric',
            'compra' => 'required'
        ];
    }

    public function preparingSaving($user, $order, $number, $location, $date, $state): void
    {
        $this->usuario_id = $user;
        $this->ordenMedidor = $order;
        $this->numeroMedidor = $number;
        $this->direccion = $location;
        $this->fechaInstalacion = $date;
        $this->estado = ($state == null) ? true : $state;
        $this->save();
    }

    public static function getDataUsingNumber($numberGauge)
    {
        return Medidor::where('numeroMedidor', '=', $numberGauge)->first();
    }

}
