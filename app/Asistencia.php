<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Asistencia extends Model
{
    protected  $primaryKey = 'idAsistencia';

    public function event()
    {
        return $this->hasOne('App\Evento', 'idEvento', 'evento_id');
    }
}