<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsuarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            [
                'idUsuario' => 1,
                'tipoUsuario_id' => 1,
                'persona_id' => null,
                'name' => 'adminOTB',
                'email' => 'adminotb@gmail.com',
                'password' => Hash::make('adminOTB#21'),
                'icoType' => 'Varon_1',
            ]
        ]);
    }
}
