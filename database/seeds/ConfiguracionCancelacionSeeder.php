<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConfiguracionCancelacionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('configuracion_cancelacions')->insert([
            [
                'idConfiguracionCancelacion' => 1,
                'usuario_id' => 1,
                'montoCuboAgua' => 1.00,
                'montoMultaConsumoAgua' => 30.00,
                'montoTransferenciaAccion' => 250.00,
                'montoMinimoCancelacion' => 10.00,
                'descuentoCobroAgua' => 'DESCUENTO POR SARS-COV-2=>2020-02-01 00:00:00=>2020-10-31 00:00:00=>0',
                'cantidadMesesParaMulta' => 3,
                'fechaActualizacion' => now(),
                'activo' =>  true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
