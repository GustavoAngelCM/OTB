<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHistorialProFondosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('historial_pro_fondos', function (Blueprint $table) {
            $table->engine = "InnoDB";
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
            $table->increments('idHistorialProFondo');
            $table->integer('usuario_id')->unsigned();
            $table->integer('profondo_id')->unsigned();
            $table->integer('cancelacion_id')->nullable()->unsigned()->default(null);
            $table->float('montoCancelacion', 8, 2)->unsigned()->default(0.00);
            $table->dateTime('fechaHistorialProFondo')->default(now());
            $table->enum('state', [
                'PENDING',
                'IN_PROCESS',
                'COMPLETED',
                'CANCELLED',
            ])->default('PENDING');
            $table->foreign('usuario_id')
                ->references('idUsuario')
                ->on('users')
                ->onDelete('cascade');
            $table->foreign('profondo_id')
                ->references('idProfondo')
                ->on('profondos')
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
        Schema::dropIfExists('historial_pro_fondos');
    }
}
