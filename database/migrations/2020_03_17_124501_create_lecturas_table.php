<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLecturasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lecturas', function (Blueprint $table) {
            $table->engine = "InnoDB";
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
            $table->increments('idLectura');
            $table->integer('medidor_id');
            $table->integer('usuario_id');
            $table->integer('medida');
            $table->dateTime('fechaMedicion');
            $table->enum('estado',[
                'INITIAL',
                'NORMAL',
            ]);
            $table->foreign('medidor_id')
                ->references('idMedidor')
                ->on('medidors')
                ->onDelete('cascade');
            $table->foreign('usuario_id')
                ->references('idUsuario')
                ->on('users')
                ->onDelete('cascade');
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
        Schema::dropIfExists('lecturas');
    }
}
