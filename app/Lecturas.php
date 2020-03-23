<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Lecturas extends Model
{
    protected  $primaryKey = 'idLectura';

    public function historyAll()
    {
        return $this->hasMany('App\HistorialCancelacion', 'lectura_id', 'idLectura');
    }
}
