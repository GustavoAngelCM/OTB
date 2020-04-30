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
                'descuentoCobroAgua' => 'DESCUENTO POR SARS-CoV-2=>2020-03-01=>2020-04-31=>50',
                'cantidadMesesParaMulta' => 3,
                'fechaActualizacion' => now(),
                'activo' =>  true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
