<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Evento
 *
 * @property int $idEvento
 * @property int $usuario_id
 * @property string $nombreEvento
 * @property string $descripcionEvento
 * @property float $montoMulta
 * @property int $finalizado
 * @property string $fechaEvento
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Evento newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Evento newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Evento query()
 * @method static \Illuminate\Database\Eloquent\Builder|Evento whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Evento whereDescripcionEvento($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Evento whereFechaEvento($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Evento whereFinalizado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Evento whereIdEvento($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Evento whereMontoMulta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Evento whereNombreEvento($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Evento whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Evento whereUsuarioId($value)
 * @mixin \Eloquent
 */
class Evento extends Model
{
    protected  $primaryKey = 'idEvento';
}
