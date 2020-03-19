<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TipoUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('tipo_usuarios')->insert([
            [
                'idTipoUsuario' => 1,
                'nombreTipoUsuario' => 'ADMINISTRADOR'
            ],
            [
                'idTipoUsuario' => 2,
                'nombreTipoUsuario' => 'SOCIO'
            ],
            [
                'idTipoUsuario' => 3,
                'nombreTipoUsuario' => 'DIRECTIVO'
            ]
        ]);
    }
}
