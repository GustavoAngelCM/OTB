<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Medidor extends Model
{
    protected  $primaryKey = 'idMedidor';

    public function readings()
    {
        return $this->hasMany('App\Lecturas', 'medidor_id', 'idMedidor');
    }

    public function readingsPending()
    {
        return $this->hasMany('App\Lecturas', 'medidor_id', 'idMedidor');
    }

}
