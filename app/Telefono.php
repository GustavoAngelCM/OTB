<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Telefono extends Model
{
    protected  $primaryKey = 'idTelefono';

    public static function inputRulesPhone($phoneNumber)
    {
        return [
            'numeroTelefono' => $phoneNumber,
        ];
    }

    public static function rulesPhone()
    {
        return [
            'numeroTelefono' => 'bail|required|unique:telefonos',
        ];
    }

    public function preparingSaving($person, $numberPhone)
    {
        $this->persona_id = $person;
        $this->numeroTelefono = $numberPhone;
        $this->save();
    }

}
