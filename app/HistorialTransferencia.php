<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\HistorialTransferencia
 *
 * @property int $idHistorialTransferencias
 * @property int|null $usuario_anterior_id
 * @property int $usuario_siguiente_id
 * @property int $medidor_involucrado_id
 * @property int|null $cancelacion_id
 * @property float $montoTotalTransferencia
 * @property float $montoCancelado
 * @property string $fechaHoraTransaferencia
 * @property string $estadoTransferencia
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|HistorialTransferencia newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|HistorialTransferencia newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|HistorialTransferencia query()
 * @method static \Illuminate\Database\Eloquent\Builder|HistorialTransferencia whereCancelacionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HistorialTransferencia whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HistorialTransferencia whereEstadoTransferencia($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HistorialTransferencia whereFechaHoraTransaferencia($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HistorialTransferencia whereIdHistorialTransferencias($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HistorialTransferencia whereMedidorInvolucradoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HistorialTransferencia whereMontoCancelado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HistorialTransferencia whereMontoTotalTransferencia($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HistorialTransferencia whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HistorialTransferencia whereUsuarioAnteriorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HistorialTransferencia whereUsuarioSiguienteId($value)
 * @mixin \Eloquent
 */
class HistorialTransferencia extends Model
{
    protected  $primaryKey = 'idHistorialTransferencias';

    public function preparingSaving($userNext, $gauge, $cancellation, $price, $userPrev = null): void
    {
        $this->usuario_anterior_id = $userPrev;
        $this->usuario_siguiente_id = $userNext;
        $this->medidor_involucrado_id = $gauge;
        $this->cancelacion_id = $cancellation;
        $this->montoTotalTransferencia = ConfiguracionCancelacion::activeConfiguration()->montoTransferenciaAccion - $price;
        $this->montoCancelado = $price;
        $this->estadoTransferencia = ($this->montoTotalTransferencia > 0) ? 'IN_PROCESS' : 'COMPLETED';
        $this->save();
    }

}
