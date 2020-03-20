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
                'nombreTipoUsuario' => 'ADMINISTRADOR',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'idTipoUsuario' => 2,
                'nombreTipoUsuario' => 'SOCIO',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'idTipoUsuario' => 3,
                'nombreTipoUsuario' => 'DIRECTIVO',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
