<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHistorialCancelacionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('historial_cancelacions', function (Blueprint $table) {
            $table->engine = "InnoDB";
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
            $table->increments('idHistorialCancelaciones');
            $table->integer('lectura_id')->unsigned();
            $table->integer('cancelacion_id')->nullable()->unsigned();
            $table->integer('diferenciaMedida')->unsigned();
            $table->float('precioUnidad', 8, 2)->unsigned();
            $table->float('subTotal', 10, 2)->unsigned()->default(0.00);
            $table->float('montoCancelado', 10, 2)->unsigned()->default(0.00);
            $table->dateTime('fechaHoraHCancelacion')->default(now());
            $table->enum('estadoMedicion', [
                'PENDING',
                'IN_PROCESS',
                'COMPLETED',
                'CANCELLED',
            ])->default('PENDING');
            $table->foreign('lectura_id')
                ->references('idLectura')
                ->on('lecturas')
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
        Schema::dropIfExists('historial_cancelacions');
    }
}
