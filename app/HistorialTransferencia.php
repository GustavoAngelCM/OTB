<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\HistorialTransferencia
 *
 * @property int $idHistorialTransferencias
 * @property int|null $usuario_anterior_id
 * @property int $usuario_siguiente_id
 * @property int $medidor_involucrado_id
 * @property int|null $cancelacion_id
 * @property float $montoTotalTransferencia
 * @property float $montoCancelado
 * @property string $fechaHoraTransaferencia
 * @property string $estadoTransferencia
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\User|null $previousUser
 * @property-read \App\User|null $currentUser
 * @property-read \App\Medidor|null $gauge
 * @property-read \App\Cancelacion|null $cancellation
 * @method static \Illuminate\Database\Eloquent\Builder|HistorialTransferencia newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|HistorialTransferencia newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|HistorialTransferencia query()
 * @method static \Illuminate\Database\Eloquent\Builder|HistorialTransferencia whereCancelacionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HistorialTransferencia whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HistorialTransferencia whereEstadoTransferencia($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HistorialTransferencia whereFechaHoraTransaferencia($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HistorialTransferencia whereIdHistorialTransferencias($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HistorialTransferencia whereMedidorInvolucradoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HistorialTransferencia whereMontoCancelado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HistorialTransferencia whereMontoTotalTransferencia($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HistorialTransferencia whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HistorialTransferencia whereUsuarioAnteriorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HistorialTransferencia whereUsuarioSiguienteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HistorialTransferencia id($id)
 * @mixin \Eloquent
 */
class HistorialTransferencia extends Model
{
    protected  $primaryKey = 'idHistorialTransferencias';

    public function previousUser(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(User::class,  'idUsuario', 'usuario_anterior_id');
    }

    public function currentUser(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(User::class,  'idUsuario', 'usuario_siguiente_id');
    }

    public function gauge(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Medidor::class,  'idMedidor', 'medidor_involucrado_id');
    }

    public function cancellation(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Cancelacion::class,  'idCancelacion', 'cancelacion_id');
    }

    public function preparingSaving($userNext, $gauge, $cancellation, $price, $userPrev = null): void
    {
        $this->usuario_anterior_id = $userPrev;
        $this->usuario_siguiente_id = $userNext;
        $this->medidor_involucrado_id = $gauge;
        $this->cancelacion_id = $cancellation;
        $this->montoTotalTransferencia = ConfiguracionCancelacion::activeConfiguration()->montoTransferenciaAccion;
        $this->montoCancelado = $price;
        $this->estadoTransferencia = ($this->montoTotalTransferencia > $this->montoCancelado) ? 'IN_PROCESS' : 'COMPLETED';
        $this->fechaHoraTransaferencia = now();
        $this->save();
    }

    public static function _instanceAndSaving($userNext, $gauge, $cancellation, $price, $userPrev = null) : void
    {
        $cancellationNew = new self();
        $cancellationNew->preparingSaving($userNext, $gauge, $cancellation, $price, $userPrev);
    }

    public static function userData($user): ?array
    {
        $_user = null;
        if ($user)
        {
            $_user = [
                'uid' => $user->name,
                'ico' => $user->icoType,
                'fullName' => $user->person->fullName(),
                'ci' => $user->person->ciExp()
            ];
        }
        return $_user;
    }

    public static function gaugeData($gauge): ?array
    {
        $_gauge = null;
        if ($gauge)
        {
            $_gauge = [
                'id' => $gauge->idMedidor,
                'order_gauge' => $gauge->ordenMedidor,
                'number_gauge' => $gauge->numeroMedidor,
                'direction' => $gauge->direccion
            ];
        }
        return $_gauge;
    }

    public static function cancellationData($cancellation): ?array
    {
        $_cancellation = null;
        if ($cancellation)
        {
            $_cancellation = [
                'id' => $cancellation->idCancelacion,
                'mount' => $cancellation->montoCancelacion,
                'date' => $cancellation->fechaCancelacion,
                'key' => $cancellation->keyCancelacion,
                'coin' => $cancellation->moneda
            ];
        }
        return $_cancellation;
    }

    public static function inputRulesPayment($transaction, $cancellation, $mountOrGauge, $payment = true) : array
    {
        $inputRules = [
            'transaction' => $transaction,
            'cancellation' => $cancellation
        ];
        if ($payment)
        {
            $inputRules = array_merge($inputRules, ['mount' => $mountOrGauge]);
        }
        else
        {
            $inputRules = array_merge($inputRules, ['gauge' => $mountOrGauge]);
        }
        return $inputRules;
    }

    public static function rulesPayment($payment = true) : array
    {
        $rules = [
            'transaction' => 'bail|required|numeric|exists:historial_transferencias,idHistorialTransferencias',
            'cancellation' => 'bail|required|numeric|exists:cancelacions,idCancelacion'
        ];
        if ($payment)
        {
            $rules = array_merge($rules, ['mount' => 'bail|required|numeric']);
        }
        else
        {
            $rules = array_merge($rules, ['gauge' => 'bail|required|numeric|exists:medidors,idMedidor']);
        }
        return $rules;
    }

    public function scopeId($query, $id)
    {
        return $query->where('idHistorialTransferencias', $id);
    }
}
