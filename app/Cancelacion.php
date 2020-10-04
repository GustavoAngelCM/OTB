<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;

class Cancelacion extends Model
{
    protected  $primaryKey = 'idCancelacion';

    public function historyCancellation(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany('App\HistorialCancelacion', 'cancelacion_id', 'idCancelacion');
    }

    public function historyAssists(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany('App\Asistencia', 'cancelacion_id', 'idCancelacion');
    }

    public function historyProBackground(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany('App\HistorialProFondo', 'cancelacion_id', 'idCancelacion');
    }

    public function historyProTransfers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany('App\HistorialTransferencia', 'cancelacion_id', 'idCancelacion');
    }

    public function changeCoin($coin, $amount)
    {
        $changeCoinRequest = Http::get('https://api.cambio.today/v1/full/BOB/json?key=4234|S^9b_2vNDkPjc~eR1Dr^4q3Y2fZfJxAA');
        $changeCoin = $changeCoinRequest->json()['result']['conversion'];
        if ($coin !== 'BOLIVIANOS')
        {
            $objToChangeCoin = null;
            foreach ($changeCoin as $valor)
            {
                if ($coin === 'DOLARES' && $valor['to'] == 'USD')
                {
                    $objToChangeCoin = $valor;
                    break;
                }
                if ($coin === 'EUROS' && $valor['to'] == 'EUR')
                {
                    $objToChangeCoin = $valor;
                    break;
                }
            }
            if ($objToChangeCoin !== null)
            {
                $amount /= $objToChangeCoin['rate'];
            }
        }
        return $amount;
    }

    public function getDataPartnerReadingToCancellation()
    {
        return self::where('keyCancelacion', $this->keyCancelacion)
            ->join('historial_cancelacions', 'cancelacions.idCancelacion', '=', 'historial_cancelacions.cancelacion_id')
            ->join('lecturas', 'historial_cancelacions.lectura_id', '=', 'lecturas.idLectura')
            ->join('medidors', 'lecturas.medidor_id', '=', 'medidors.idMedidor')
            ->join('users', 'medidors.usuario_id', '=', 'users.idUsuario')
            ->join('personas', 'users.persona_id', '=', 'idPersona')
            ->selectRaw('concat_ws(" ", personas.pNombre, personas.sNombre, personas.apellidoP, personas.apellidoM) as fullName, medidors.ordenMedidor as orden, medidors.numeroMedidor as numero, concat_ws("****", users.name, personas.ci) as secret')
            ->get()
            ->first();
    }

    public function getDataCancellation($key = null)
    {
        return Cancelacion::where('keyCancelacion', '=', ($key) ? $key: $this->keyCancelacion)
            ->select(
                'idCancelacion as numero',
                'descartado',
                'descuento',
                'fechaCancelacion as fecha',
                'keyCancelacion as codigo',
                'moneda',
                'montoCancelacion as monto',
                'tipoCancelacion as tipo'
            )
            ->get()->first();
    }

    public static function getIDCancellation($key)
    {
        return Cancelacion::where('keyCancelacion', '=', $key)
            ->select(
                'idCancelacion'
            )
            ->get()->first();
    }

    public static function rulesPrint()
    {
        return [
            'keyCancelacion' => 'bail|required|exists:cancelacions'
        ];
    }

    public static function rulesCancellation()
    {
        return [
            'cancellations' => 'bail|required',
            'keyCancelacion' => 'bail|required|unique:cancelacions'
        ];
    }

    public static function rulesBindingToReading(): array
    {
        return [
            'idLectura' => 'bail|required|numeric',
            'monto' => 'bail|required|numeric',
            'multa' => 'bail|required|numeric',
            'moneda' => 'bail|required',
            'tipo' => 'bail|required',
        ];
    }

    public function prepareSaving($total, $key, $moneda, $tipo): void
    {
        $this->montoCancelacion = $total;
        $this->keyCancelacion = $key;
        $this->moneda = strtoupper($moneda);
        $this->tipoCancelacion = strtoupper($tipo);
        $this->fechaCancelacion = now();
        $this->save();
    }

    public static function inputRulesPrint($key): array
    {
        return [
            'keyCancelacion' => $key
        ];
    }

    public static function inputRulesCancellation($cancellations, $key): array
    {
        return [
            'cancellations' => $cancellations,
            'keyCancelacion' => $key
        ];
    }

    public static function inputRulesBindingToReading($key, $mount, $fine, $coin, $type): array
    {
        return [
            'idLectura' => $key,
            'monto' => $mount,
            'multa' => $fine,
            'moneda' => $coin,
            'tipo' => $type
        ];
    }

    public static function infoGaugeHistoryForCancellation($key)
    {
        return self::where('keyCancelacion', '=', $key)->get()->first()->historyCancellation()
            ->join('lecturas', 'historial_cancelacions.lectura_id', '=', 'lecturas.idLectura')
            ->select(
                'historial_cancelacions.idHistorialCancelaciones',
                'historial_cancelacions.lectura_id',
                'historial_cancelacions.cancelacion_id',
                'historial_cancelacions.diferenciaMedida',
                'historial_cancelacions.estadoMedicion',
                'lecturas.fechaMedicion as fechaHoraHCancelacion',
                'historial_cancelacions.montoCancelado',
                'historial_cancelacions.precioUnidad',
                'historial_cancelacions.subTotal',
                'lecturas.medida as lecturaActual'
            )
            ->get();
    }

    public static function mountAndFineDataBindingHistoryForCancellation($key): array
    {
        $dataHistories = self::infoGaugeHistoryForCancellation($key);

        $dataHistoriesFines = [];

        foreach ($dataHistories as $history)
        {
            $fineData = null;
            foreach ($dataHistories as $history_sub)
            {
                if ($history->lectura_id === $history_sub->lectura_id && $history_sub->precioUnidad === 0.0)
                {
                    $fineData = $history_sub;
                }
            }
            if ($history->precioUnidad !== 0.0)
            {
                $dataHistoriesFines[] = [
                    'medida' => $history->diferenciaMedida,
                    'anteriorActual' => ($history->lecturaActual - $history->diferenciaMedida) . " - $history->lecturaActual",
                    'estado' => $history->estadoMedicion,
                    'fecha' => $history->fechaHoraHCancelacion,
                    'monto' => $history->montoCancelado,
                    'precio' => $history->precioUnidad,
                    'subTotal' => $history->subTotal,
                    'multa' => ($fineData === null) ? null : [
                        'medida' => $fineData->diferenciaMedida,
                        'estado' => $fineData->estadoMedicion,
                        'fecha' => $fineData->fechaHoraHCancelacion,
                        'monto' => $fineData->montoCancelado,
                        'precio' => $fineData->precioUnidad,
                        'subTotal' => $fineData->subTotal
                    ]
                ];
            }
        }
        return $dataHistoriesFines;
    }

    public static function calculatedTotalCancelled($key)
    {
        $mountsInProcess = HistorialCancelacion::inProcessTransaction($key);
        $total = 0;
        if (count($mountsInProcess) > 0)
        {
            foreach ($mountsInProcess as $clave => $valor)
            {
                $total += $valor['monto'];
            }
        }
        return $total;
    }

    public static function statePercentageLogicCancellation($total, $subTotal, $discount, $percentage): string
    {
        return ($total < $subTotal) ?
            (
                (
                    $discount
                    &&
                    (
                        ($subTotal * ((double) ('0.'.$percentage))) == $total
                    )
                ) ?
                    'COMPLETED' :
                    'IN_PROCESS'
            ) :
            'COMPLETED';
    }

    public static function subTotalLogicCancellation($difference, $mountCube, $minimumAmount)
    {
        return (
                ($difference * $mountCube) === 0
            ) ?
            $minimumAmount
            :
            ($difference * $mountCube);
    }

    public static function changeCoinAPI($url, $to, $to_2, $name, $name_2)
    {
        $changeCoinRequestBOB = Http::get($url);
        $listChangeCoinBO = array();
        foreach ($changeCoinRequestBOB->json()['result']['conversion'] as $coin)
        {
            if (($coin['to'] === $to) || ($coin['to'] === $to_2))
            {
                $listChangeCoinBO[($coin['to'] === $to)?$name:$name_2] = $coin;
            }
        }
        return $listChangeCoinBO;
    }

    public static function inputRulesGaugeTransaction($cancellation, $key): array
    {
        return [
            'montoCancelacion' => $cancellation['precio'],
            'moneda' => $cancellation['moneda'],
            'tipoCancelacion' => $cancellation['tipo'],
            'keyCancelacion' => $key,
        ];
    }

    public static function rulesGaugeTransaction(): array
    {
        return [
            'montoCancelacion' => 'bail|required|numeric',
            'moneda' => 'bail|required',
            'tipoCancelacion' => 'bail|required',
            'keyCancelacion' => 'bail|required|unique:cancelacions'
        ];
    }

    public static function currencyAbbreviation($coin): ?string
    {
        switch ($coin)
        {
            case "BOLIVIANOS": return "BS";
            case "DOLARES": return "USD";
            case "EUROS": return "EUR";
            default: return "UNKNOWN";
        }
    }

}
