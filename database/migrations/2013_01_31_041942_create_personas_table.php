<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePersonasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('personas', function (Blueprint $table) {
            $table->charset = 'latin1';
            $table->collation = 'latin1_bin';
            $table->increments('idPersona');
            $table->string('pNombre', 20);
            $table->string('sNombre', 35)->nullable();
            $table->string('apellidoP', 20);
            $table->string('apellidoM', 35)->nullable();
            $table->string('ci', 15);
            $table->enum('expxedicion',
                [
                    'SANTA CRUZ',
                    'LA PAZ',
                    'COCHABAMBA',
                    'POTOSI',
                    'SUCRE',
                    'PANDO',
                    'BENI',
                    'TARIJA',
                    'ORURO',
                    'EXTRANJERO',
                ]
            );
            $table->date('fechaNacimiento');
            $table->enum('sexo', ['F', 'M', 'O']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('personas');
    }
}
