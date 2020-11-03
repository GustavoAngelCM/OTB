<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Profondo
 *
 * @property int $idProfondo
 * @property string $nombreProfondo
 * @property string|null $descripcionProfondo
 * @property float $montoEstablecido
 * @property string $estado
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Profondo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Profondo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Profondo query()
 * @method static \Illuminate\Database\Eloquent\Builder|Profondo whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Profondo whereDescripcionProfondo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Profondo whereEstado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Profondo whereIdProfondo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Profondo whereMontoEstablecido($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Profondo whereNombreProfondo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Profondo whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Profondo extends Model
{
    protected  $primaryKey = 'idProfondo';
}
