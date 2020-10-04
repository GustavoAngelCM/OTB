<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Lecturas extends Model
{
    protected  $primaryKey = 'idLectura';

    public function historyAll()
    {
        return $this->hasMany('App\HistorialCancelacion', 'lectura_id', 'idLectura');
    }

    public static function findReading($key)
    {
        return Lecturas::where('idLectura', $key)->select('fechaMedicion')->get()->first();
    }

    public function preparingSaving($gauge, $userAuth, $reading, $date)
    {
        $this->medidor_id = $gauge;
        $this->usuario_id = $userAuth;
        $this->medida = $reading;
        $this->fechaMedicion = ($date === null) ? now() : $date; //Carbon::parse('2020-02-02');//parseando una fecha medicion ficticia pero de origen de lecturas
        $this->save();
    }

    public static function searchCancellationsToReading($key, $mounts = false)
    {
        return Lecturas::where('idLectura', $key)
            ->join('historial_cancelacions', 'lecturas.idLectura', '=', 'historial_cancelacions.lectura_id')
            ->join('cancelacions', 'historial_cancelacions.cancelacion_id', '=', 'cancelacions.idCancelacion')
            ->distinct('historial_cancelacions.cancelacion_id')
            ->where('historial_cancelacions.estadoMedicion', '!=', 'CANCELLED')
            ->select('cancelacions.keyCancelacion', 'cancelacions.montoCancelacion', 'cancelacions.moneda', ($mounts) ? 'historial_cancelacions.montoCancelado' : 'cancelacions.idCancelacion')
            ->get();
    }

}
