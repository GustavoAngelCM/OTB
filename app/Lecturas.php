<?php

namespace App;

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

    public static function findReading($key)
    {
        return self::where('idLectura', $key)->select('fechaMedicion')->first();
    }

    public function preparingSaving($gauge, $userAuth, $reading, $date): void
    {
        $this->medidor_id = $gauge;
        $this->usuario_id = $userAuth;
        $this->medida = $reading;
        $this->fechaMedicion = $date ?? now(); //Carbon::parse('2020-02-02');//parseando una fecha medicion ficticia pero de origen de lecturas
        $this->save();
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

}
