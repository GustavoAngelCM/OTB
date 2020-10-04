<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ConfiguracionCancelacion extends Model
{
    protected  $primaryKey = 'idConfiguracionCancelacion';

    public static function activeConfiguration($select = false)
    {
        return ($select) ?
            ConfiguracionCancelacion::where('activo', '=', 1)
                ->select(
                    'montoCuboAgua as montoCubo',
                    'montoMultaConsumoAgua as multaConsumo',
                    'montoTransferenciaAccion as transferenciaAccion',
                    'montoMinimoCancelacion as montoMinimo',
                    'descuentoCobroAgua as descuentoAgua',
                    'cantidadMesesParaMulta as mesesMulta',
                    'fechaActualizacion'
                )
                ->orderBy('created_at', 'desc')
                ->get()
                ->first()
            :
            ConfiguracionCancelacion::where('activo', '=', 1)->orderBy('created_at', 'desc')->get()->first();
    }
}
