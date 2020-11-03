<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\HistorialCancelacion
 *
 * @property int $idHistorialCancelaciones
 * @property int $lectura_id
 * @property int|null $cancelacion_id
 * @property int $diferenciaMedida
 * @property float $precioUnidad
 * @property float $subTotal
 * @property float $montoCancelado
 * @property string $fechaHoraHCancelacion
 * @property string $estadoMedicion
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Cancelacion|null $cancellation
 * @method static \Illuminate\Database\Eloquent\Builder|HistorialCancelacion fine()
 * @method static \Illuminate\Database\Eloquent\Builder|HistorialCancelacion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|HistorialCancelacion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|HistorialCancelacion query()
 * @method static \Illuminate\Database\Eloquent\Builder|HistorialCancelacion whereCancelacionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HistorialCancelacion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HistorialCancelacion whereDiferenciaMedida($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HistorialCancelacion whereEstadoMedicion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HistorialCancelacion whereFechaHoraHCancelacion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HistorialCancelacion whereIdHistorialCancelaciones($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HistorialCancelacion whereLecturaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HistorialCancelacion whereMontoCancelado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HistorialCancelacion wherePrecioUnidad($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HistorialCancelacion whereSubTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HistorialCancelacion whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class HistorialCancelacion extends Model
{
    protected  $primaryKey = 'idHistorialCancelaciones';

    public function cancellation(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne('App\Cancelacion', 'idCancelacion', 'cancelacion_id');
    }

    public function prepareSaving($lectura_id, $cancelacion_id, $diferenciaMedida, $precioUnidad, $subTotal, $montoCancelado, $estadoMedicion): void
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
        return self::where('lectura_id', '=', $key)
            ->where('estadoMedicion',  '!=', 'CANCELLED')
            ->where('historial_cancelacions.diferenciaMedida',   '!=', 0)
            ->where('historial_cancelacions.precioUnidad',   '!=', 0)
            ->orderBy('created_at', 'desc')
            ->first();
    }

    public static function inProcessTransaction($key)
    {
        return self::where('lectura_id', '=', $key)
            ->where('estadoMedicion', 'IN_PROCESS')
            ->select('montoCancelado as monto')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public static function pendingValidFine($key)
    {
        return self::where('lectura_id', $key)
            ->where('estadoMedicion',  '=', 'PENDING')
            ->where('diferenciaMedida',  '=', 0)
            ->where('precioUnidad',  '=', 0)
            ->orderBy('created_at', 'desc')
            ->get()
            ->first();
    }

    public static function _instanceAndSaving($key, $id, $difference, $priceUnit, $subTotal, $mount, $state): void
    {
        $fineHistoryCancellation = new self();
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
