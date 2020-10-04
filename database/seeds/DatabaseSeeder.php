<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(array(
            TipoUserSeeder::class,
            PersonasTableSeeder::class,
            UsersTableSeeder::class,
            ConfiguracionCancelacionSeeder::class,
            MedidorsTableSeeder::class,
            LecturasTableSeeder::class,
            CancelacionsTableSeeder::class,
            HistorialTransferenciasTableSeeder::class,
            HistorialCancelacionsTableSeeder::class,
            EventosTableSeeder::class,
            AsistenciasTableSeeder::class
        ));
    }
}
