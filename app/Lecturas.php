<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Lecturas
 *
 * @property int $idLectura
 * @property int $medidor_id
 * @property int $usuario_id
 * @property int $medida
 * @property string $fechaMedicion
 * @property string $estado
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\HistorialCancelacion[] $historyAll
 * @property-read int|null $history_all_count
 * @method static \Illuminate\Database\Eloquent\Builder|Lecturas newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Lecturas newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Lecturas query()
 * @method static \Illuminate\Database\Eloquent\Builder|Lecturas whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lecturas whereEstado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lecturas whereFechaMedicion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lecturas whereIdLectura($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lecturas whereMedida($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lecturas whereMedidorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lecturas whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lecturas whereUsuarioId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lecturas reading($key)
 * @method static \Illuminate\Database\Eloquent\Builder|Lecturas gauge($gauge)
 * @mixin \Eloquent
 */
class Lecturas extends Model
{
    protected  $primaryKey = 'idLectura';

    public function historyAll(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany('App\HistorialCancelacion', 'lectura_id', 'idLectura');
    }

    public function scopeReading($query, $key)
    {
        return $query->where('idLectura', $key);
    }

    public function scopeGauge($query, $gauge)
    {
        return $query->where('medidor_id', $gauge);
    }

    public static function findReading($key)
    {
        return self::where('idLectura', $key)->select('fechaMedicion')->first();
    }

    public static function _instanceAndSaving($gauge, $userAuth, $reading, $date, $state = 'NORMAL'): void
    {
        $newReading = new self();
        $newReading->preparingSaving($gauge, $userAuth, $reading, $date, $state);
    }

    public function preparingSaving($gauge, $userAuth, $reading, $date, $state = 'NORMAL'): void
    {
        $this->medidor_id = $gauge;
        $this->usuario_id = $userAuth;
        $this->medida = $reading;
        $this->fechaMedicion = $date ?? now(); //Carbon::parse('2020-02-02');//parseando una fecha medicion ficticia pero de origen de lecturas
        $this->estado = $state;
        $this->save();
    }

    public static function readingsToLevel($dateLimit, $dateFlag, $gaugeReading, $configCancellations, $gauge, $user_auth): void
    {
        while ($dateLimit > $dateFlag)
        {
            $gaugeReading += $configCancellations->montoMinimoCancelacion;
            $dateFlag->setMonth($dateFlag->month + 1);
            $reading = new self();
            $reading->preparingSaving($gauge->idMedidor, $user_auth->idUsuario, $gaugeReading, $dateFlag);
            HistorialCancelacion::_instanceAndSaving($reading->idLectura, null, $configCancellations->montoMinimoCancelacion, $configCancellations->montoCuboAgua, ($configCancellations->montoMinimoCancelacion * $configCancellations->montoCuboAgua), 0, 'PENDING');
        }
    }

    public static function searchCancellationsToReading($key, $mounts = false)
    {
        return self::where('idLectura', $key)
            ->join('historial_cancelacions', 'lecturas.idLectura', '=', 'historial_cancelacions.lectura_id')
            ->join('cancelacions', 'historial_cancelacions.cancelacion_id', '=', 'cancelacions.idCancelacion')
            ->distinct('historial_cancelacions.cancelacion_id')
            ->where('historial_cancelacions.estadoMedicion', '!=', 'CANCELLED')
            ->where('cancelacions.descartado', '!=', 1)
            ->select([
                'cancelacions.keyCancelacion',
                'cancelacions.montoCancelacion',
                'cancelacions.moneda',
                ($mounts) ? 'historial_cancelacions.montoCancelado' : 'cancelacions.idCancelacion'
            ])
            ->get();
    }

    public static function previousReading($key, $id)
    {
        return self::where('medidor_id', '=', $key)
            ->where('idLectura', '<', $id)
            ->orderBy('created_at', 'desc')
            ->first();
    }

}
