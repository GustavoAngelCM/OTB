<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCancelacionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cancelacions', function (Blueprint $table) {
            $table->engine = "InnoDB";
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
            $table->increments('idCancelacion');
            $table->float('montoCancelacion', 8, 2)->unsigned();
            $table->dateTime('fechaCancelacion')->default(now());
            $table->string('keyCancelacion', 15);
            $table->boolean('descartado')->default(false);
            $table->enum('tipoCancelacion',[
                'BOLIVIANOS',
                'DOLARES',
                'DEPOSITO',
                'CHEQUE',
            ])->default('BOLIVIANOS');
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
        Schema::dropIfExists('cancelacions');
    }
}
