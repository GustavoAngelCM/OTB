<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HistorialTransferencia extends Model
{
    protected  $primaryKey = 'idHistorialTransferencias';

    public function preparingSaving($userNext, $gauge, $cancellation, $balance, $price, $userPrev = null)
    {
        $this->usuario_anterior_id = $userPrev;
        $this->usuario_siguiente_id = $userNext;
        $this->medidor_involucrado_id = $gauge;
        $this->cancelacion_id = $cancellation;
        $this->montoTotalTransferencia = (isset($balance)) ? $balance + $price : $price;
        $this->montoCancelado = $price;
        $this->estadoTransferencia = (isset($balance)) ? 'IN_PROCESS' : 'COMPLETED';
        $this->save();
    }

}
