<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Persona extends Model
{
    protected  $primaryKey = 'idPersona';

    public function fullName(): string
    {
        return ($this->sNombre === null) ? ( ($this->apellidoM === null) ? "{$this->pNombre} {$this->apellidoP}" : "{$this->pNombre} {$this->apellidoP} {$this->apellidoM}" ) : ( ($this->apellidoM === null) ? "{$this->pNombre} {$this->sNombre} {$this->apellidoP}" : "{$this->pNombre} {$this->sNombre} {$this->apellidoP} {$this->apellidoM}" ) ;
    }

    public function shortName(): string
    {
        return "{$this->pNombre} {$this->apellidoP}";
    }

    public function seterNames($names)
    {
        [$this->pNombre, $this->sNombre] = self::wordDivider($names);
    }

    public function seterLastNames($lastnames)
    {
        [$this->apellidoP, $this->apellidoM] = self::wordDivider($lastnames);
    }


    public static function wordDivider($words)
    {
        $words = explode(' ', trim($words));
        $first_word = '';
        $sword = '';
        for ($i = 0; $i < count($words); $i++){
            if ( $i === 0 ) {
                $first_word = $words[$i];
            } else {
                if ($i === 1) {
                    $sword = $words[$i];
                } else {
                    $sword = "{$sword} {$words[$i]}";;
                }
            }
        }
        return[
            ucfirst($first_word),
            ($sword==="") ? null : ucwords($sword)
        ];
    }
    public function seterCIEXP($ciexp)
    {
        [$this->ci, $this->expxedicion] = self::dividerCiExp($ciexp);
    }

    public static function dividerCiExp($ciexp)
    {
        [$ci, $exp] = self::wordDivider($ciexp);
        if (strpos($exp, ' ') === true)
        {
            return [
                $ci,
                'COCHABAMBA'
            ];
        }
        else
        {
            switch (strtoupper($exp))
            {
                case 'LP': $exp = 'LA PAZ'; break;
                case 'SCZ': $exp = 'SANTA CRUZ'; break;
                case 'OR': $exp = 'ORURO'; break;
                case 'PN': $exp = 'PANDO'; break;
                case 'TJ': $exp = 'TARIJA'; break;
                case 'PO': $exp = 'POTOSI'; break;
                case 'SU': $exp = 'SUCRE'; break;
                case 'BE': $exp = 'BENI'; break;
                case 'EXT': $exp = 'EXTRANJERO'; break;
                default: $exp = 'COCHABAMBA'; break;
            }
            return [
                $ci,
                $exp
            ];
        }
    }

    public function user()
    {
        return $this->hasOne('App\User', 'persona_id', 'idPersona');
    }

    public function phones()
    {
        return $this->hasMany('App\Telefono', 'persona_id', 'idPersona');
    }

    public function preparingSaving($nombres, $apellidos, $ci, $fechaNacimiento, $sexo)
    {
        $this->seterNames($nombres);
        $this->seterLastNames($apellidos);
        $this->seterCIEXP($ci);
        $this->fechaNacimiento = $fechaNacimiento;
        $this->sexo = $sexo;
        $this->save();
    }

    public static function getData($ci)
    {
        return Persona::where('ci', '=', $ci)->first();
    }

    public static function updatingFields($id, $fields)
    {
        $fieldsForUpdating = [];
        foreach ($fields as $key => $field)
        {
            switch ($key)
            {
                case "nombres": [$fieldsForUpdating['pNombre'], $fieldsForUpdating['sNombre']] = self::wordDivider($field); break;
                case "apellidos": [$fieldsForUpdating['apellidoP'], $fieldsForUpdating['apellidoM']] = self::wordDivider($field); break;
                case "ci": [$fieldsForUpdating['ci'], $fieldsForUpdating['expxedicion']] = self::dividerCiExp($field); break;
                case "sexo": $fieldsForUpdating['sexo'] = strtoupper($field); break;
                case "fechaNacimiento": $fieldsForUpdating['fechaNacimiento'] = Carbon::parse($field); break;
            }
        }
        return Persona::where('idPersona', $id)->update($fieldsForUpdating);
    }

    public static function existsRelationPersonGauges($id)
    {
        $response = Persona::where('idPersona', $id)->first()->user()->first()->gauges()->get();
        return (count($response) < 1) ? false : true;
    }

    public function ciExp()
    {
        switch ($this->expxedicion)
        {
            case 'LA PAZ': $exp = 'LP'; break;
            case 'SANTA CRUZ': $exp = 'SCZ'; break;
            case 'ORURO': $exp = 'OR' ; break;
            case 'PANDO': $exp = 'PN' ; break;
            case 'TARIJA': $exp = 'TJ' ; break;
            case 'POTOSI': $exp = 'PO' ; break;
            case 'SUCRE': $exp = 'SU' ; break;
            case 'BENI': $exp = 'BE' ; break;
            case 'EXTRANJERO': $exp = 'EXT' ; break;
            default: $exp = 'CBBA'; break;
        }
        return "{$this->ci} {$exp}";
    }

}
