<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * App\User
 *
 * @property int $idUsuario
 * @property int $tipoUsuario_id
 * @property int|null $persona_id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string $icoType
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Medidor[] $gauges
 * @property-read int|null $gauges_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Medidor[] $gaugesOrder
 * @property-read int|null $gauges_order_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read \App\Persona|null $person
 * @property-read \App\TipoUsuario|null $tipo
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereIcoType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereIdUsuario($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePersonaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTipoUsuarioId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUpdatedAt($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|User uid($uid)
 * @method static \Illuminate\Database\Eloquent\Builder|User innerJoinsToPerson()
 */
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

    public function tipo(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne('App\TipoUsuario',  'idTipoUsuario', 'tipoUsuario_id');
    }

    public function person(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne('App\Persona',  'idPersona', 'persona_id');
    }

    public function gauges(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany('App\Medidor', 'usuario_id', 'idUsuario');
    }

    public function gaugesOrder(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany('App\Medidor', 'usuario_id', 'idUsuario')->orderByRaw('CAST(medidors.ordenMedidor AS INT) ASC');
    }

    public function scopeUid($query, $uid)
    {
        return $query->where('name', urldecode($uid));
    }

    public function scopeInnerJoinsToPerson($query)
    {
        return $query->join('users', 'users.persona_id', '=', 'personas.idPersona');
    }

    public static function userGetForName($name)
    {
        return self::where('name', urldecode($name))->get()->first();
    }

    public static function personGetForName($name)
    {
        return self::where('name', urldecode($name))->get()->first()->person()->first();
    }

    public static function personGet_userGetForName($person_userGet)
    {
        return $person_userGet->person()->selectRaw('concat_ws(" ", pNombre, sNombre, apellidoP, apellidoM) as fullName')->get()->first();
    }

    public static function gaugesGet_userGetForName($gauges_userGet)
    {
        return $gauges_userGet->gauges()->get();
    }

    public static function inputRulesUser($type, $names, $lastNames, $ci, $dateOfBirth, $sex, $email, $ico, $gauges, $phones): array
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

    public static function rulesUser(): array
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

    public function preparingSaving($typeUser, $person, $email, $ico): void
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
        return self::where('persona_id', '=', $persona)->first();
    }

    public static function getUsersManagers()
    {
        return self::select(['persona_id', 'icoType'])->where('tipoUsuario_id', 3)->with('person:pNombre,sNombre,apellidoM,apellidoP,idPersona')->get();
    }

    public static function updatingFields($id, $fields)
    {
        $fieldsForUpdating = [];
        foreach ($fields as $key => $field)
        {
            switch ($key)
            {
                case "email": $fieldsForUpdating['email'] = $field; break;
                case "ico": $fieldsForUpdating['icoType'] = $field; break;
                case "tipo": $fieldsForUpdating['tipoUsuario_id'] = $field; break;
            }
        }
        return self::where('idUsuario', $id)->update($fieldsForUpdating);
    }

}
