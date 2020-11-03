<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\ConfiguracionCancelacion
 *
 * @property int $idConfiguracionCancelacion
 * @property int $usuario_id
 * @property float $montoCuboAgua
 * @property float $montoMultaConsumoAgua
 * @property float $montoTransferenciaAccion
 * @property float $montoMinimoCancelacion
 * @property int $cantidadMesesParaMulta
 * @property string|null $descuentoCobroAgua
 * @property string $fechaActualizacion
 * @property int $activo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ConfiguracionCancelacion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ConfiguracionCancelacion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ConfiguracionCancelacion query()
 * @method static \Illuminate\Database\Eloquent\Builder|ConfiguracionCancelacion whereActivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ConfiguracionCancelacion whereCantidadMesesParaMulta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ConfiguracionCancelacion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ConfiguracionCancelacion whereDescuentoCobroAgua($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ConfiguracionCancelacion whereFechaActualizacion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ConfiguracionCancelacion whereIdConfiguracionCancelacion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ConfiguracionCancelacion whereMontoCuboAgua($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ConfiguracionCancelacion whereMontoMinimoCancelacion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ConfiguracionCancelacion whereMontoMultaConsumoAgua($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ConfiguracionCancelacion whereMontoTransferenciaAccion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ConfiguracionCancelacion whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ConfiguracionCancelacion whereUsuarioId($value)
 * @mixin \Eloquent
 */
class ConfiguracionCancelacion extends Model
{
    protected  $primaryKey = 'idConfiguracionCancelacion';

    public static function activeConfiguration($select = false)
    {
        return ($select) ?
            self::where('activo', '=', 1)
                ->select([
                    'montoCuboAgua as montoCubo',
                    'montoMultaConsumoAgua as multaConsumo',
                    'montoTransferenciaAccion as transferenciaAccion',
                    'montoMinimoCancelacion as montoMinimo',
                    'descuentoCobroAgua as descuentoAgua',
                    'cantidadMesesParaMulta as mesesMulta',
                    'fechaActualizacion'
                ])
                ->orderBy('created_at', 'desc')
                ->get()
                ->first()
            :
            self::where('activo', '=', 1)->orderBy('created_at', 'desc')->get()->first();
    }
}
