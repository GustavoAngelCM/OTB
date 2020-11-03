<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Medidor
 *
 * @property int $idMedidor
 * @property int $usuario_id
 * @property string $ordenMedidor
 * @property string $numeroMedidor
 * @property string $direccion
 * @property string $fechaInstalacion
 * @property string $estado
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Asistencia[] $assists
 * @property-read int|null $assists_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Lecturas[] $readings
 * @property-read int|null $readings_count
 * @method static \Illuminate\Database\Eloquent\Builder|Medidor newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Medidor newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Medidor query()
 * @method static \Illuminate\Database\Eloquent\Builder|Medidor whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Medidor whereDireccion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Medidor whereEstado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Medidor whereFechaInstalacion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Medidor whereIdMedidor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Medidor whereNumeroMedidor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Medidor whereOrdenMedidor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Medidor whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Medidor whereUsuarioId($value)
 * @mixin \Eloquent
 */
class Medidor extends Model
{
    protected  $primaryKey = 'idMedidor';

    public function readings(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany('App\Lecturas', 'medidor_id', 'idMedidor')->orderBy('fechaMedicion', 'desc');
    }

    public function assists(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany('App\Asistencia', 'medidor_id', 'idMedidor')->orderBy('fechaHoraAsistencia', 'desc')->orderBy('idAsistencia', 'desc');
    }

    public static function inputRulesGauges($gauge): array
    {
        return [
//            'ordenMedidor' => $gauge['orden'],
            'numeroMedidor' => $gauge['numero'],
            'direccion' => $gauge['direccion'],
            'fechaInstalacion' => $gauge['fechaInstalacion'],
            'fechaNivelacion' => $gauge['fechaNivelacion'],
            'estado' => $gauge['estado'],
            'medida' => $gauge['lectura'],
            'compra' => $gauge['compra']
        ];
    }

    public static function rulesGauges(): array
    {
        return [
//            'ordenMedidor' => 'bail|required|unique:medidors',
            'numeroMedidor' => 'bail|required|unique:medidors',
            'direccion' => 'bail|required|max:150',
            'fechaInstalacion' => 'bail|required|date',
            'fechaNivelacion' => 'bail|required|date',
            'estado' => 'bail|nullable|boolean',
            'medida' => 'bail|required|numeric',
            'compra' => 'required'
        ];
    }

    public function preparingSaving($user, $number, $location, $date, $state): void
    {
        $this->usuario_id = $user;
        $this->ordenMedidor = (string)(self::ultimateOrderGauge() + 1);
        $this->numeroMedidor = $number;
        $this->direccion = $location;
        $this->fechaInstalacion = $date;
        $this->estado = $state ?? true;
        $this->save();
    }

    public static function ultimateOrderGauge()
    {
        return self::selectRaw('MAX(CAST(ordenMedidor AS INT)) as orden ')->first()->orden;
    }

    public static function getDataUsingNumber($numberGauge)
    {
        return self::where('numeroMedidor', '=', $numberGauge)->first();
    }

}
