<?php

use Illuminate\Database\Seeder;

class EventosTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('eventos')->delete();
        
        \DB::table('eventos')->insert(array (
            0 => 
            array (
                'idEvento' => 1,
                'usuario_id' => 1,
                'nombreEvento' => 'Reunion 1',
                'descripcionEvento' => 'reunion de emergencia de agua 1',
                'montoMulta' => 50.0,
                'finalizado' => 1,
                'fechaEvento' => '2020-09-28 12:48:32',
                'created_at' => '2020-09-28 12:48:32',
                'updated_at' => '2020-09-28 12:48:32',
            ),
            1 => 
            array (
                'idEvento' => 2,
                'usuario_id' => 1,
                'nombreEvento' => 'Reunion 2',
                'descripcionEvento' => 'reunion de emergencia de agua 2',
                'montoMulta' => 50.0,
                'finalizado' => 1,
                'fechaEvento' => '2020-09-28 12:48:32',
                'created_at' => '2020-09-28 12:48:32',
                'updated_at' => '2020-09-28 12:48:32',
            ),
            2 => 
            array (
                'idEvento' => 3,
                'usuario_id' => 1,
                'nombreEvento' => 'Reunion 3',
                'descripcionEvento' => 'reunion de emergencia de agua 3',
                'montoMulta' => 50.0,
                'finalizado' => 1,
                'fechaEvento' => '2020-09-28 12:48:32',
                'created_at' => '2020-09-28 12:48:32',
                'updated_at' => '2020-09-28 12:48:32',
            ),
            3 => 
            array (
                'idEvento' => 4,
                'usuario_id' => 1,
                'nombreEvento' => 'Reunion 4',
                'descripcionEvento' => 'reunion de emergencia de agua 4',
                'montoMulta' => 50.0,
                'finalizado' => 1,
                'fechaEvento' => '2020-09-28 12:48:32',
                'created_at' => '2020-09-28 12:48:32',
                'updated_at' => '2020-09-28 12:48:32',
            ),
            4 => 
            array (
                'idEvento' => 5,
                'usuario_id' => 1,
                'nombreEvento' => 'Reunion 5',
                'descripcionEvento' => 'reunion de emergencia de agua 5',
                'montoMulta' => 50.0,
                'finalizado' => 1,
                'fechaEvento' => '2020-09-28 12:48:33',
                'created_at' => '2020-09-28 12:48:33',
                'updated_at' => '2020-09-28 12:48:33',
            ),
            5 => 
            array (
                'idEvento' => 6,
                'usuario_id' => 1,
                'nombreEvento' => 'Reunion 6',
                'descripcionEvento' => 'reunion de emergencia de agua 6',
                'montoMulta' => 50.0,
                'finalizado' => 1,
                'fechaEvento' => '2020-09-28 12:48:33',
                'created_at' => '2020-09-28 12:48:33',
                'updated_at' => '2020-09-28 12:48:33',
            ),
            6 => 
            array (
                'idEvento' => 7,
                'usuario_id' => 1,
                'nombreEvento' => 'Reunion 7',
                'descripcionEvento' => 'reunion de emergencia de agua 7',
                'montoMulta' => 50.0,
                'finalizado' => 1,
                'fechaEvento' => '2020-09-28 12:48:33',
                'created_at' => '2020-09-28 12:48:33',
                'updated_at' => '2020-09-28 12:48:33',
            ),
            7 => 
            array (
                'idEvento' => 8,
                'usuario_id' => 1,
                'nombreEvento' => 'Reunion 8',
                'descripcionEvento' => 'reunion de emergencia de agua 8',
                'montoMulta' => 50.0,
                'finalizado' => 1,
                'fechaEvento' => '2020-09-28 12:48:33',
                'created_at' => '2020-09-28 12:48:33',
                'updated_at' => '2020-09-28 12:48:33',
            ),
            8 => 
            array (
                'idEvento' => 9,
                'usuario_id' => 1,
                'nombreEvento' => 'Reunion 9',
                'descripcionEvento' => 'reunion de emergencia de agua 9',
                'montoMulta' => 50.0,
                'finalizado' => 1,
                'fechaEvento' => '2020-09-28 12:48:33',
                'created_at' => '2020-09-28 12:48:33',
                'updated_at' => '2020-09-28 12:48:33',
            ),
            9 => 
            array (
                'idEvento' => 10,
                'usuario_id' => 1,
                'nombreEvento' => 'Reunion 10',
                'descripcionEvento' => 'reunion de emergencia de agua 10',
                'montoMulta' => 50.0,
                'finalizado' => 0,
                'fechaEvento' => '2020-09-28 12:48:33',
                'created_at' => '2020-09-28 12:48:33',
                'updated_at' => '2020-09-28 12:48:33',
            ),
        ));
        
        
    }
}