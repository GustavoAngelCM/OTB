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
}
