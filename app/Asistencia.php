<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Asistencia
 *
 * @property int $idAsistencia
 * @property int $medidor_id
 * @property int $evento_id
 * @property int $asistio
 * @property int|null $cancelacion_id
 * @property string|null $fechaHoraAsistencia
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Evento|null $event
 * @property-read \App\Medidor|null $gauge
 * @property-read \App\Cancelacion|null $payment
 * @method static \Illuminate\Database\Eloquent\Builder|Asistencia newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Asistencia newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Asistencia query()
 * @method static \Illuminate\Database\Eloquent\Builder|Asistencia whereAsistio($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Asistencia whereCancelacionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Asistencia whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Asistencia whereEventoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Asistencia whereFechaHoraAsistencia($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Asistencia whereIdAsistencia($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Asistencia whereMedidorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Asistencia whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Asistencia assistance($key)
 * @method static \Illuminate\Database\Eloquent\Builder|Asistencia theyAttend($attend = true)
 * @method static \Illuminate\Database\Eloquent\Builder|Asistencia eventRelation($event)
 * @method static \Illuminate\Database\Eloquent\Builder|Asistencia GaugeRelation($gauge)
 * @method static \Illuminate\Database\Eloquent\Builder|Asistencia isNotCancelled()
 * @method static \Illuminate\Database\Eloquent\Builder|Asistencia isCancelled()
 * @mixin \Eloquent
 */
class Asistencia extends Model
{
    protected  $primaryKey = 'idAsistencia';

    public function event(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne('App\Evento', 'idEvento', 'evento_id');
    }

    public function gauge(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne('App\Medidor', 'idMedidor', 'medidor_id');
    }

    public function payment(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne('App\Cancelacion', 'idCancelacion', 'cancelacion_id');
    }

    public static function _instanceAndSaving($gauge, $event, $attended, $dateTime) : void
    {
        $assistance = new self();
        $assistance->medidor_id = $gauge;
        $assistance->evento_id = $event;
        $assistance->asistio = $attended;
        $assistance->cancelacion_id = null;
        $assistance->fechaHoraAsistencia = $dateTime;
        $assistance->save();
    }

    public function scopeAssistance($query, $key)
    {
        return $query->where('idAsistencia', $key);
    }

    public function scopeTheyAttend($query, $attend = true)
    {
        return $query->where('asistio', $attend);
    }

    public function scopeEventRelation($query, $event)
    {
        return $query->where('evento_id', $event);
    }

    public function scopeGaugeRelation($query, $gauge)
    {
        return $query->where('medidor_id', $gauge);
    }

    public function scopeIsNotCancelled($query)
    {
        return $query->whereNull('cancelacion_id');
    }

    public function scopeIsCancelled($query)
    {
        return $query->whereNotNull('cancelacion_id');
    }

}
