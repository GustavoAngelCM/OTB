<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\HistorialProFondo
 *
 * @property int $idHistorialProFondo
 * @property int $medidor_id
 * @property int $profondo_id
 * @property int|null $cancelacion_id
 * @property float $montoCancelacion
 * @property string $fechaHistorialProFondo
 * @property string $state
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|HistorialProFondo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|HistorialProFondo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|HistorialProFondo query()
 * @method static \Illuminate\Database\Eloquent\Builder|HistorialProFondo whereCancelacionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HistorialProFondo whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HistorialProFondo whereFechaHistorialProFondo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HistorialProFondo whereIdHistorialProFondo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HistorialProFondo whereMedidorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HistorialProFondo whereMontoCancelacion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HistorialProFondo whereProfondoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HistorialProFondo whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HistorialProFondo whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class HistorialProFondo extends Model
{
    protected  $primaryKey = 'idHistorialProFondo';
}
