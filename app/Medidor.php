<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

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
 * @method static \Illuminate\Database\Eloquent\Builder|Medidor numberGauge($number)
 * @method static \Illuminate\Database\Eloquent\Builder|Medidor id($id)
 * @method static \Illuminate\Database\Eloquent\Builder|Medidor innerJoinToReading()
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

    public function user(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne('App\User', 'idUsuario', 'usuario_id');
    }

    public static function inputRulesGauges($gauge): array
    {
        return [
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
            'numeroMedidor' => 'bail|required|unique:medidors',
            'direccion' => 'bail|required|max:150',
            'fechaInstalacion' => 'bail|required|date',
            'fechaNivelacion' => 'bail|required|date',
            'estado' => 'bail|nullable|boolean',
            'medida' => 'bail|required|numeric',
            'compra' => 'required'
        ];
    }

    public static function inputRulesGaugeEdit($number, $direction, $state, $measure, $id): array
    {
        return [
            'id' => $id,
            'numberGauge' => $number,
            'direction' => $direction,
            'state' => $state,
            'measure' => $measure
        ];
    }

    public static function rulesGaugeEdit($id): array
    {
        return [
            'id' => 'bail|required|exists:medidors,idMedidor',
            'numberGauge' => [
                'bail',
                'required',
                'max:50',
                Rule::unique('medidors', 'numeroMedidor')->ignore($id, 'idMedidor')
            ],
            'direction' => 'bail|required|max:150',
            'state' => 'bail|nullable|in:ACTIVO,PASIVO,INACTIVO',
            'measure' => 'bail|nullable|numeric'
        ];
    }

    public static function inputRulesGaugeDelete($number): array
    {
        return [
            'numberGauge' => $number
        ];
    }

    public static function rulesGaugeDelete(): array
    {
        return [
            'numberGauge' => 'bail|required|exists:medidors,numeroMedidor'
        ];
    }

    public static function _instanceAndSaving($user, $number, $location, $date, $state): void
    {
        $newGauge = new self();
        $newGauge->preparingSaving($user, $number, $location, $date, $state);
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

    public function scopeNumberGauge($query, $number)
    {
        return $query->where('numeroMedidor', $number);
    }

    public function scopeId($query, $id)
    {
        return $query->where('idMedidor', (int) $id);
    }

    public function scopeInnerJoinToReading($query)
    {
        return $query->join('lecturas', 'medidors.idMedidor', '=', 'lecturas.medidor_id');
    }

    public static function existsPaymentGauge($gaugeNumber): int
    {
        $countNotPayment = 0;
        $meterReadings = self::innerJoinToReading()->numberGauge($gaugeNumber)->select(['lecturas.idLectura', 'lecturas.medida', 'lecturas.fechaMedicion'])->get();
        foreach ($meterReadings as $meterReading)
        {
            $history = HistorialCancelacion::reading($meterReading->idLectura)->orderByDesc('estadoMedicion')->first();
            if ($history && $history->estadoMedicion !== 'COMPLETED')
            {
                $countNotPayment++;
            }
        }
        return $countNotPayment;
    }
}
