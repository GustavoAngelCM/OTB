<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

/**
 * App\Persona
 *
 * @property int $idPersona
 * @property string $pNombre
 * @property string|null $sNombre
 * @property string $apellidoP
 * @property string|null $apellidoM
 * @property string $ci
 * @property string $expxedicion
 * @property string $fechaNacimiento
 * @property string $sexo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Telefono[] $phones
 * @property-read int|null $phones_count
 * @property-read \App\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|Persona newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Persona newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Persona query()
 * @method static \Illuminate\Database\Eloquent\Builder|Persona whereApellidoM($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Persona whereApellidoP($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Persona whereCi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Persona whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Persona whereExpxedicion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Persona whereFechaNacimiento($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Persona whereIdPersona($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Persona wherePNombre($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Persona whereSNombre($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Persona whereSexo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Persona whereUpdatedAt($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|Persona cancelled()
 * @method static \Illuminate\Database\Eloquent\Builder|Persona dateSelect($month, $isChargePerMonth = false)
 * @method static \Illuminate\Database\Eloquent\Builder|Persona innerJoinsToCancellation()
 * @method static \Illuminate\Database\Eloquent\Builder|Persona innerJoinsToHistory()
 * @method static \Illuminate\Database\Eloquent\Builder|Persona innerJoinsToGauge()
 * @method static \Illuminate\Database\Eloquent\Builder|Persona gaugeOrder()
 * @method static \Illuminate\Database\Eloquent\Builder|Persona fine($isFine = false)
 * @method static \Illuminate\Database\Eloquent\Builder|Persona innerJoinsToAssistance()
 * @method static \Illuminate\Database\Eloquent\Builder|Persona notAssistance()
 * @method static \Illuminate\Database\Eloquent\Builder|Persona notAssistanceCancelled($cancelled = true)
 */
class Persona extends Model
{
    protected  $primaryKey = 'idPersona';

    public function fullName(): string
    {
        if ($this->sNombre === null)
        {
            return ($this->apellidoM === null) ? "{$this->pNombre} {$this->apellidoP}" : "{$this->pNombre} {$this->apellidoP} {$this->apellidoM}";
        }
        return ($this->apellidoM === null) ? "{$this->pNombre} {$this->sNombre} {$this->apellidoP}" : "{$this->pNombre} {$this->sNombre} {$this->apellidoP} {$this->apellidoM}";
    }

    public function names(): string
    {
        return ($this->sNombre === null) ? $this->pNombre : "{$this->pNombre} {$this->sNombre}";
    }

    public function lastNames(): string
    {
        return ($this->apellidoM === null) ? $this->apellidoP : "{$this->apellidoP} {$this->apellidoM}";
    }

    public function shortName(): string
    {
        return "{$this->pNombre} {$this->apellidoP}";
    }

    public function seterNames($names): void
    {
        [$this->pNombre, $this->sNombre] = self::wordDivider($names);
    }

    public function seterLastNames($lastnames): void
    {
        [$this->apellidoP, $this->apellidoM] = self::wordDivider($lastnames);
    }


    public static function wordDivider($words): array
    {
        $words = explode(' ', trim($words));
        $first_word = '';
        $sword = '';
        foreach ($words as $i => $iValue) {
            if ( $i === 0 ) {
                $first_word = $iValue;
            } else if ($i === 1) {
                $sword = $iValue;
            } else {
                $sword = "{$sword} $iValue";
            }
        }
        return[
            ucfirst($first_word),
            ($sword==="") ? null : ucwords($sword)
        ];
    }

    public function seterCIEXP($ciexp): void
    {
        [$this->ci, $this->expxedicion] = self::dividerCiExp($ciexp);
    }

    public static function dividerCiExp($ciexp): ?array
    {
        [$ci, $exp] = self::wordDivider($ciexp);
        if (strpos($exp, ' ') === true)
        {
            return [
                $ci,
                'COCHABAMBA'
            ];
        }
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

    public function user(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne('App\User', 'persona_id', 'idPersona');
    }

    public function phones(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany('App\Telefono', 'persona_id', 'idPersona');
    }

    public function preparingSaving($nombres, $apellidos, $ci, $fechaNacimiento, $sexo): void
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
        return self::where('ci', '=', $ci)->first();
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
        return self::where('idPersona', $id)->update($fieldsForUpdating);
    }

    public static function existsRelationPersonGauges($id): bool
    {
        $response = self::where('idPersona', $id)->first()->user()->first()->gauges()->get();
        return count($response) >= 1;
    }

    public function ciExp(): string
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

    public static function inputRulesUpdate($uid, $tipo, $nombres, $apellidos, $ci, $fechaNacimiento, $sexo, $email, $ico, $telefonos): array
    {
        return [
            "uid" => $uid,
            "tipo" => $tipo,
            "nombres" => $nombres,
            "apellidos" => $apellidos,
            "ci" => $ci,
            "fechaNacimiento" => $fechaNacimiento,
            "sexo" => $sexo,
            "email" => $email,
            "ico" => $ico,
            "telefonos" => $telefonos
        ];
    }

    public static function rulesUpdate($id): array
    {
        return [
            "uid" => "bail|required|exists:users,name",
            "tipo" => "bail|required|numeric",
            "nombres" => "required",
            "apellidos" => "required",
            "ci" => [
                'bail',
                'required',
                Rule::unique('personas', 'ci')->ignore($id, 'idPersona')
            ],
            "fechaNacimiento" => "bail|required|date",
            "sexo" => "bail|required|in:F,M",
            "email" => "bail|required|email",
            "ico" => "bail|required|in:Varon_1,Varon_2,Mujer_1,Mujer_2",
            "telefonos" => "bail|nullable|numeric"
        ];
    }

    public static function rulesUpdateTransfer(): array
    {
        return [
            "tipo" => "bail|required|numeric",
            "nombres" => "required",
            "apellidos" => "required",
            "ci" => "bail|required|unique:personas",
            "fechaNacimiento" => "bail|required|date",
            "sexo" => "bail|required|in:F,M",
            "email" => "bail|required|email",
            "ico" => "bail|required|in:Varon_1,Varon_2,Mujer_1,Mujer_2",
            "telefonos" => "bail|nullable|numeric"
        ];
    }

    public static function inputRulesOtherPartner($uid): array
    {
        return [
            "uid" => $uid
        ];
    }

    public static function rulesOtherPartner(): array
    {
        return [
            "uid" => "bail|required|exists:users,name"
        ];
    }

    public static function inputRulesReport($month, $payment, $fine, $assistance): array
    {
        return [
            "month" => $month,
            "payment" => $payment,
            "fine" => $fine,
            "assistance" => $assistance
        ];
    }

    public static function rulesReport($isAmountPerMonth): array
    {
        if (is_string($isAmountPerMonth))
        {
            return [
                "month" => "string",
                "payment" => "in:AMOUNT_PER_MONTH",
                "fine" => "in:AMOUNT_PER_MONTH",
                "assistance" => "in:AMOUNT_PER_MONTH"
            ];
        }
        return [
            "month" => "nullable",
            "payment" => "bail|boolean|nullable",
            "fine" => "bail|boolean|nullable",
            "assistance" => "bail|boolean|nullable"
        ];
    }

    public static function inputRulesPartnerExcept($uid) : array
    {
        return [
            "name" => $uid
        ];
    }

    public static function rulesPartnerExcept() : array
    {
        return [
            "name" => "bail|required|exists:users"
        ];
    }

    public function scopeInnerJoinsToGauge($query)
    {
        return $query->join('users', 'users.persona_id', '=', 'personas.idPersona')
            ->join('medidors', 'medidors.usuario_id', '=', 'users.idUsuario');
    }

    public function scopeInnerJoinsToHistory($query)
    {
        return $query->join('users', 'users.persona_id', '=', 'personas.idPersona')
            ->join('medidors', 'medidors.usuario_id', '=', 'users.idUsuario')
            ->join('lecturas', 'lecturas.medidor_id', '=', 'medidors.idMedidor')
            ->join('historial_cancelacions', 'historial_cancelacions.lectura_id', '=', 'lecturas.idLectura');
    }

    public function scopeInnerJoinsToCancellation($query)
    {
        return $query->join('users', 'users.persona_id', '=', 'personas.idPersona')
            ->join('medidors', 'medidors.usuario_id', '=', 'users.idUsuario')
            ->join('lecturas', 'lecturas.medidor_id', '=', 'medidors.idMedidor')
            ->join('historial_cancelacions', 'historial_cancelacions.lectura_id', '=', 'lecturas.idLectura')
            ->join('cancelacions', 'cancelacions.idCancelacion', '=', 'historial_cancelacions.cancelacion_id');
    }

    public function scopeInnerJoinsToAssistance($query)
    {
        return $query->join('users', 'users.persona_id', '=', 'personas.idPersona')
            ->join('medidors', 'medidors.usuario_id', '=', 'users.idUsuario')
            ->join('asistencias', 'asistencias.medidor_id', '=', 'medidors.idMedidor')
            ->join('eventos', 'eventos.idEvento', '=', 'asistencias.evento_id');
    }

    public function scopeFine($query, $isFine = false)
    {
        return $query->where('historial_cancelacions.diferenciaMedida',  ($isFine) ? '=' : '!=', 0)
            ->where('historial_cancelacions.precioUnidad',  ($isFine) ? '=' : '!=', 0);
    }

    public function scopeNotAssistance($query)
    {
        return $query->where('asistencias.asistio', 0);
    }

    public function scopeNotAssistanceCancelled($query, $cancelled = true)
    {
        return ($cancelled) ? $query->whereNotNull('asistencias.cancelacion_id') : $query->whereNull('asistencias.cancelacion_id');
    }

    public function scopeDateSelect($query, $month, $isChargePerMonth = false)
    {
        if (strpos($month, ' - '))
        {
            [$init, $end] = explode(' - ', $month);
            return $query->whereBetween('cancelacions.fechaCancelacion', [Carbon::parse($init), Carbon::parse($end)->endOfDay()]);
        }
        if ($isChargePerMonth)
        {
            return $query->whereYear('cancelacions.fechaCancelacion', $month->year)
                ->whereMonth('cancelacions.fechaCancelacion', $month->month);
        }
        return $query->whereYear('lecturas.fechaMedicion', $month->year)
            ->whereMonth('lecturas.fechaMedicion', $month->month);
    }

    public function scopeCancelled($query)
    {
        return $query->where('historial_cancelacions.estadoMedicion', '!=', 'CANCELLED');
    }

    public function scopeGaugeOrder($query)
    {
        return $query->orderByRaw('CAST(medidors.ordenMedidor AS INT) ASC');
    }

    public static function  reportsManagement($month, $payment, $fine, $assistance)
    {
        Carbon::setLocale('es');
        if ($month === null)
        {
            $month = now();
        }
        else if (!strpos($month, ' - '))
        {
            $month = Carbon::parse($month);
        }
        if ($payment === null && $fine === null && $assistance === null)
        {
            return self::innerJoinsToGauge()
                ->gaugeOrder()
                ->selectRaw(
                    'concat_ws(" ", personas.pNombre, personas.sNombre, personas.apellidoP, personas.apellidoM) as fullName, '.
                    '(medidors.ordenMedidor) as order_gauge, '.
                    '(medidors.numeroMedidor) as number_gauge'
                )
                ->get();
        }
        if ($payment === 'AMOUNT_PER_MONTH' && $fine === 'AMOUNT_PER_MONTH' && $assistance === 'AMOUNT_PER_MONTH')
        {
            return self::innerJoinsToCancellation()
                ->dateSelect($month, true)
                ->cancelled()
                ->gaugeOrder()
                ->selectRaw(
                    'concat_ws(" ", personas.pNombre, personas.sNombre, personas.apellidoP, personas.apellidoM) as fullName, '.
                    '(medidors.ordenMedidor) as order_gauge, '.
                    '(medidors.numeroMedidor) as number_gauge, '.
                    '(lecturas.fechaMedicion) as key_transaction, '.
                    '(historial_cancelacions.montoCancelado) as mount'
                )
                ->get()
                ->map(function ($item) {
                    $month = Carbon::parse($item->key_transaction);
                    $item->key_transaction = strtoupper("{$month->monthName} de {$month->year}");
                    return $item;
                });
        }
        if ($payment !== null)
        {
            if ($payment)
            {
                $parameter = strpos($month, ' - ') ? '(historial_cancelacions.montoCancelado) as mount' : '(cancelacions.montoCancelacion) as mount';
                if ($fine)
                {
                    return self::innerJoinsToCancellation()
                        ->dateSelect($month)
                        ->fine()
                        ->cancelled()
                        ->gaugeOrder()
                        ->selectRaw(
                            'concat_ws(" ", personas.pNombre, personas.sNombre, personas.apellidoP, personas.apellidoM) as fullName, '.
                            '(medidors.ordenMedidor) as order_gauge, '.
                            '(medidors.numeroMedidor) as number_gauge, '.
                            '(lecturas.fechaMedicion) as key_transaction, '.
                            $parameter
                        )
                        ->get()
                        ->map(function ($item) {
                            $month = Carbon::parse($item->key_transaction);
                            $item->key_transaction = strtoupper("{$month->monthName} de {$month->year}");
                            return $item;
                        });
                }
                return self::innerJoinsToCancellation()
                    ->dateSelect($month)
                    ->cancelled()
                    ->gaugeOrder()
                    ->selectRaw(
                        'concat_ws(" ", personas.pNombre, personas.sNombre, personas.apellidoP, personas.apellidoM) as fullName, '.
                        '(medidors.ordenMedidor) as order_gauge, '.
                        '(medidors.numeroMedidor) as number_gauge, '.
                        '(lecturas.fechaMedicion) as key_transaction, '.
                        $parameter
                    )
                    ->get()
                    ->map(function ($item) {
                        $month = Carbon::parse($item->key_transaction);
                        $item->key_transaction = strtoupper("{$month->monthName} de {$month->year}");
                        return $item;
                    });

            }
            return self::innerJoinsToHistory()
                ->dateSelect($month)
                ->fine()
                ->cancelled()
                ->gaugeOrder()
                ->havingRaw('key_transaction is null')
                ->groupByRaw('order_gauge')
                ->selectRaw(
                    'concat_ws(" ", personas.pNombre, personas.sNombre, personas.apellidoP, personas.apellidoM) as fullName, '.
                    '(medidors.ordenMedidor) as order_gauge, '.
                    '(medidors.numeroMedidor) as number_gauge, '.
                    'max(historial_cancelacions.cancelacion_id) as key_transaction'
                )
                ->get();
        }
        if ($fine !== null)
        {
            if ($fine)
            {
                return self::innerJoinsToCancellation()
                    ->dateSelect($month)
                    ->fine(true)
                    ->cancelled()
                    ->gaugeOrder()
                    ->selectRaw(
                        'concat_ws(" ", personas.pNombre, personas.sNombre, personas.apellidoP, personas.apellidoM) as fullName, '.
                        '(medidors.ordenMedidor) as order_gauge, '.
                        '(medidors.numeroMedidor) as number_gauge, '.
                        '(lecturas.fechaMedicion) as key_transaction, '.
                        '(cancelacions.montoCancelacion) as mount'
                    )
                    ->get()
                    ->map(function ($item) {
                        $month = Carbon::parse($item->key_transaction);
                        $item->key_transaction = strtoupper("{$month->monthName} de {$month->year}");
                        return $item;
                    });
            }
            return self::innerJoinsToHistory()
                ->dateSelect($month)
                ->fine(true)
                ->cancelled()
                ->gaugeOrder()
                ->havingRaw('key_transaction is null')
                ->groupByRaw('order_gauge')
                ->selectRaw(
                    'concat_ws(" ", personas.pNombre, personas.sNombre, personas.apellidoP, personas.apellidoM) as fullName, '.
                    '(medidors.ordenMedidor) as order_gauge, '.
                    '(medidors.numeroMedidor) as number_gauge, '.
                    'max(historial_cancelacions.cancelacion_id) as key_transaction'
                )
                ->get();
        }
        if ($assistance !== null)
        {
            if ($assistance)
            {
                return self::innerJoinsToAssistance()
                    ->gaugeOrder()
                    ->notAssistance()
                    ->notAssistanceCancelled(true)
                    ->selectRaw(
                        'concat_ws(" ", personas.pNombre, personas.sNombre, personas.apellidoP, personas.apellidoM) as fullName, '.
                        '(medidors.ordenMedidor) as order_gauge, '.
                        '(medidors.numeroMedidor) as number_gauge, '.
                        'eventos.nombreEvento as name_event'
                    )
                    ->get();
            }
            return self::innerJoinsToAssistance()
                ->gaugeOrder()
                ->notAssistance()
                ->notAssistanceCancelled(false)
                ->selectRaw(
                    'concat_ws(" ", personas.pNombre, personas.sNombre, personas.apellidoP, personas.apellidoM) as fullName, '.
                    '(medidors.ordenMedidor) as order_gauge, '.
                    '(medidors.numeroMedidor) as number_gauge, '.
                    'eventos.nombreEvento as name_event'
                )
                ->get();
        }
        return [
            "error" => "Error en los parametros de petición"
        ];
    }
}
