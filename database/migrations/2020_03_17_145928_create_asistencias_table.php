<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAsistenciasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('asistencias', function (Blueprint $table) {
            $table->engine = "InnoDB";
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
            $table->increments('idAsistencia');
            $table->integer('medidor_id')->unsigned();
            $table->integer('evento_id')->unsigned();
            $table->boolean('asistio');
            $table->integer('cancelacion_id')->nullable()->unsigned();
            $table->dateTime('fechaHoraAsistencia')->default(now())->nullable();
            $table->foreign('medidor_id')
                ->references('idMedidor')
                ->on('medidors')
                ->onDelete('cascade');
            $table->foreign('evento_id')
                ->references('idEvento')
                ->on('eventos')
                ->onDelete('cascade');
            $table->foreign('cancelacion_id')
                ->references('idCancelacion')
                ->on('cancelacions')
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
        Schema::dropIfExists('asistencias');
    }
}
