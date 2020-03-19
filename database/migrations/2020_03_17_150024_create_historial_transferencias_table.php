<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHistorialTransferenciasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('historial_transferencias', function (Blueprint $table) {
            $table->engine = "InnoDB";
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
            $table->increments('idHistorialTransferencias');
            $table->integer('usuario_anterior_id')->unsigned();
            $table->integer('usuario_siguiente_id')->unsigned();
            $table->integer('cancelacion_id')->unsigned();
            $table->dateTime('fechaHoraTransaferencia')->default(now());
            $table->foreign('usuario_anterior_id')
                ->references('idUsuario')
                ->on('users')
                ->onDelete('cascade');
            $table->foreign('usuario_siguiente_id')
                ->references('idUsuario')
                ->on('users')
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
        Schema::dropIfExists('historial_transferencias');
    }
}
