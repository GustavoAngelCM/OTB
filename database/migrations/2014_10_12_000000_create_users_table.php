<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->engine = "InnoDB";
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
            $table->increments('idUsuario');
            $table->integer('tipoUsuario_id');
            $table->integer('persona_id');
            $table->string('name', 20)->unique();
            $table->string('email', 35)->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password', 220);
            $table->enum('icoType', [
                'Varon_1',
                'Varon_2',
                'Mujer_1',
                'Mujer_2',
            ]);
            $table->foreign('persona_id')
                ->references('idPersona')
                ->on('personas')
                ->onDelete('cascade');
            $table->foreign('tipoUsuario_id')
                ->references('idTipoUsuario')
                ->on('tipo_usuarios')
                ->onDelete('cascade');
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
