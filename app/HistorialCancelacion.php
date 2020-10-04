<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HistorialCancelacion extends Model
{
    protected  $primaryKey = 'idHistorialCancelaciones';

    public function cancellation()
    {
        return $this->hasOne('App\Cancelacion', 'idCancelacion', 'cancelacion_id');
    }

    public function prepareSaving($lectura_id, $cancelacion_id, $diferenciaMedida, $precioUnidad, $subTotal, $montoCancelado, $estadoMedicion)
    {
        $this->lectura_id = $lectura_id;
        $this->cancelacion_id = $cancelacion_id;
        $this->diferenciaMedida = $diferenciaMedida;
        $this->precioUnidad = $precioUnidad;
        $this->subTotal = $subTotal;
        $this->montoCancelado = $montoCancelado;
        $this->estadoMedicion = $estadoMedicion;
        $this->fechaHoraHCancelacion = now();
        $this->save();
    }

    public static function pendingValid($key)
    {
        return HistorialCancelacion::where('lectura_id', '=', $key)
            ->where('estadoMedicion',  '!=', 'CANCELLED')
            ->orderBy('created_at', 'desc')
            ->get()
            ->first();
    }

    public static function inProcessTransaction($key)
    {
        return HistorialCancelacion::where('lectura_id', '=', $key)
            ->where('estadoMedicion', 'IN_PROCESS')
            ->select('montoCancelado as monto')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public static function pendingValidFine($key)
    {
        return HistorialCancelacion::where('lectura_id', $key)
            ->where('estadoMedicion',  '=', 'PENDING')
            ->where('diferenciaMedida',  '=', 0)
            ->where('precioUnidad',  '=', 0)
            ->orderBy('created_at', 'desc')
            ->get()
            ->first();
    }

    public static function _instanceAndSaving($key, $id, $difference, $priceUnit, $subTotal, $mount, $state)
    {
        $fineHistoryCancellation = new HistorialCancelacion();
        $fineHistoryCancellation->prepareSaving(
            $key,
            $id,
            $difference,
            $priceUnit,
            $subTotal,
            $mount,
            $state
        );
    }
}
