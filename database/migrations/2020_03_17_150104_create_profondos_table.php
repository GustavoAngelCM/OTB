<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProfondosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('profondos', function (Blueprint $table) {
            $table->engine = "InnoDB";
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
            $table->increments('idProfondo');
            $table->string('nombreProfondo', 50);
            $table->string('descripcionProfondo', 300)->nullable();
            $table->float('montoEstablecido', 8,2)->unsigned();
            $table->enum('estado', [
                'PENDING',
                'IN_PROCESS',
                'COMPLETED',
                'CANCELLED',
            ]);
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
        Schema::dropIfExists('profondos');
    }
}
