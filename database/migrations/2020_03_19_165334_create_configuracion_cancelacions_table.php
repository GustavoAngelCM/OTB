<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConfiguracionCancelacionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('configuracion_cancelacions', function (Blueprint $table) {
            $table->engine = "InnoDB";
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
            $table->increments('idConfiguracionCancelacion');
            $table->integer('usuario_id')->unsigned();
            $table->float('montoCuboAgua', 8, 2);
            $table->float('montoMultaConsumoAgua', 8, 2);
            $table->float('montoTransferenciaAccion', 10, 2);
            $table->integer('cantidadMesesParaMulta');
            $table->dateTime('fechaActualizacion')->default(now());
            $table->boolean('activo');
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
        Schema::dropIfExists('configuracion_cancelacions');
    }
}
