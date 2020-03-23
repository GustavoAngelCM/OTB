<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Persona extends Model
{
    protected  $primaryKey = 'idPersona';

    public function seterNames($names)
    {
        $nombres = explode(' ', trim($names));
        $snombre = '';
        for ($i = 0; $i < count($nombres); $i++){
            if ( $i === 0 ) {
                $this->pNombre = ucfirst($nombres[$i]);
            } else {
                if ($i === 1) {
                    $snombre = $nombres[$i];
                } else {
                    $snombre = " {$nombres[$i]}";
                }
                $this->sNombre = ucfirst($snombre);
            }
        }
    }

    public function seterLastNames($lastnames)
    {
        $apellidos = explode(' ', trim($lastnames));
        $sapellido = '';
        for ($i = 0; $i < count($apellidos); $i++){
            if ( $i === 0 ) {
                $this->apellidoP = ucfirst($apellidos[$i]);
            } else {
                if ($i === 1) {
                    $sapellido = $apellidos[$i];
                } else {
                    $sapellido = " {$apellidos[$i]}";
                }
                $this->apellidoM = ucfirst($sapellido);
            }
        }
    }

    public function seterCIEXP($ciexp)
    {
        $_ciexp = explode(' ', trim($ciexp));
        $this->expxedicion = 'COCHABAMBA';
        $this->ci = $_ciexp[0];
        switch (strtoupper($_ciexp[1]))
        {
            case 'LP': $this->expxedicion = 'LA PAZ'; break;
            case 'SCZ': $this->expxedicion = 'SANTA CRUZ'; break;
            case 'CBBA': $this->expxedicion = 'COCHABAMBA'; break;
            case 'OR': $this->expxedicion = 'ORURO'; break;
            case 'PN': $this->expxedicion = 'PANDO'; break;
            case 'TJ': $this->expxedicion = 'TARIJA'; break;
            case 'PO': $this->expxedicion = 'POTOSI'; break;
            case 'SU': $this->expxedicion = 'SUCRE'; break;
            case 'BE': $this->expxedicion = 'BENI'; break;
            case 'EXT': $this->expxedicion = 'EXTRANJERO'; break;
            default: break;
        }
    }

    public function user()
    {
        return $this->hasOne('App\User', 'persona_id', 'idPersona');
    }

}
