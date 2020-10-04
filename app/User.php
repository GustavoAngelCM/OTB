<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    protected  $primaryKey = 'idUsuario';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', "tipoUsuario_id", "persona_id", "icoType",
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // Rest omitted for brevity

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function tipo()
    {
        return $this->hasOne('App\TipoUsuario',  'idTipoUsuario', 'tipoUsuario_id');
    }

    public function person()
    {
        return $this->hasOne('App\Persona',  'idPersona', 'persona_id');
    }

    public function gauges()
    {
        return $this->hasMany('App\Medidor', 'usuario_id', 'idUsuario');
    }

    public static function userGetForName($name)
    {
        return User::where('name', urldecode($name))->get()->first();
    }

    public static function personGet_userGetForName($person_userGet)
    {
        return $person_userGet->person()->selectRaw('concat_ws(" ", pNombre, sNombre, apellidoP, apellidoM) as fullName')->get()->first();
    }

    public static function gaugesGet_userGetForName($gauges_userGet)
    {
        return $gauges_userGet->gauges()->get();
    }

    public static function inputRulesUser($type, $names, $lastNames, $ci, $dateOfBirth, $sex, $email, $ico, $gauges, $phones)
    {
        return [
            'idTipoUsuario' => $type,
            'nombres' => $names,
            'apellidos' => $lastNames,
            'ci' => $ci,
            'fechaNacimiento' => $dateOfBirth,
            'sexo' => $sex,
            'email' => $email,
            'ico' => $ico,
            'medidores' => $gauges,
            'telefonos' => $phones,
        ];
    }

    public static function rulesUser()
    {
        return [
            'idTipoUsuario' => 'bail|required|numeric',
            'nombres' => 'required',
            'apellidos' => 'required',
            'ci' => 'bail|required|max:15|unique:personas',
            'fechaNacimiento' => 'bail|required|date',
            'sexo' => 'bail|required|max:1',
            'email' => 'bail|required|email|max:50|unique:users',
            'ico' => 'required',
            'medidores' => 'required',
            'telefonos' => 'required'
        ];
    }

    public function preparingSaving($typeUser, $person, $email, $ico)
    {
        $this->tipoUsuario_id = $typeUser;
        $this->persona_id = $person->idPersona;
        $this->name = strtolower("{$person->pNombre[0]}{$person->pNombre[1]}{$person->pNombre[2]}{$person->ci[0]}{$person->ci[1]}{$person->ci[2]}{$person->apellidoP}");
        $this->email = $email;
        $this->password = Hash::make($person->ci);
        $this->icoType = $ico;
        $this->save();
    }

    public static function getDataUsingCI($persona)
    {
        return User::where('persona_id', '=', $persona)->first();
    }

    public static function getUsersManagers()
    {
        return User::select('persona_id', 'icoType')->where('tipoUsuario_id', 3)->with('person:pNombre,sNombre,apellidoM,apellidoP,idPersona')->get();
    }

}
