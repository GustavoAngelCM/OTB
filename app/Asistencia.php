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
 * @mixin \Eloquent
 */
class Asistencia extends Model
{
    protected  $primaryKey = 'idAsistencia';

    public function event(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne('App\Evento', 'idEvento', 'evento_id');
    }
}
