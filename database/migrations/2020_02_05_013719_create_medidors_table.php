<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMedidorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('medidors', function (Blueprint $table) {
            $table->engine = "InnoDB";
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
            $table->increments('idMedidor');
            $table->integer('usuario_id')->unsigned();
            $table->string('ordenMedidor', 50);
            $table->string('numeroMedidor', 50)->unique();
            $table->string('direccion', 70);
            $table->date('fechaInstalacion');
            $table->enum('estado',[
                'ACTIVO',
                'PASIVO',
                'INACTIVO',
            ]);
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
        Schema::dropIfExists('medidors');
    }
}
