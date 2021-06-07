<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Evento
 *
 * @property int $idEvento
 * @property int $usuario_id
 * @property string $nombreEvento
 * @property string $descripcionEvento
 * @property float $montoMulta
 * @property int $finalizado
 * @property string $fechaEvento
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Evento newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Evento newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Evento query()
 * @method static \Illuminate\Database\Eloquent\Builder|Evento whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Evento whereDescripcionEvento($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Evento whereFechaEvento($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Evento whereFinalizado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Evento whereIdEvento($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Evento whereMontoMulta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Evento whereNombreEvento($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Evento whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Evento whereUsuarioId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Evento finalized($finalized = false)
 * @method static \Illuminate\Database\Eloquent\Builder|Evento id($id)
 * @mixin \Eloquent
 */
class Evento extends Model
{
    protected  $primaryKey = 'idEvento';

    public static function inputRulesEvents($name, $description, $fine, $dateTime, $update = false, $event = 0) : array
    {
        $rulesInput =  [
            "nombreEvento" => $name,
            "descripcionEvento" => $description,
            "montoMulta" => $fine,
            "fechaEvento" => $dateTime,
        ];
        if ($update)
        {
            $rulesInputId = array("idEvento" => $event);
            return  array_merge($rulesInput, $rulesInputId);
        }
        return  $rulesInput;
    }

    public static function rulesEvents($update = false) : array
    {
        $rules = [
            "nombreEvento" => "bail|required|string|max:80",
            "descripcionEvento" => "bail|required|string|max:150",
            "montoMulta" => "bail|required|numeric",
            "fechaEvento" => "bail|required|date",
        ];
        if ($update)
        {
            $rulesId = array("idEvento" => "bail|required|numeric|exists:eventos");
            return  array_merge($rules, $rulesId);
        }
        return  $rules;
    }

    public static function inputRulesEventAssistance($gauge, $attended) : array
    {
        return [
            "asistio" => $attended,
            "idMedidor" => $gauge
        ];
    }

    public static function rulesEventAssistance() : array
    {
        return [
            "asistio" => "bail|required|boolean",
            "idMedidor" => "bail|required|numeric|exists:medidors"
        ];
    }

    public static function inputRulesGetEventAssistance($event, $getTypeEvent) : array
    {
        return [
            "idEvento" => $event,
            "tipoListaMedidorEvento" => $getTypeEvent
        ];
    }
    public static function rulesGetEventAssistance() : array
    {
        return [
            "idEvento" => "bail|required|numeric|exists:eventos",
            "tipoListaMedidorEvento" => "bail|required|in:ALL,PAYMENT,NOT_PAYMENT,THEY_ATTEND,THEY_DID_NOT_ATTEND"
        ];
    }

    public static function inputRulesDeleteEvents($event) : array
    {
        return [
            "idEvento" => $event
        ];
    }

    public static function rulesDeleteEvents() : array
    {
        return [
            "idEvento" => "bail|required|numeric|exists:eventos"
        ];
    }

    public static function inputRulesAssists($assists, $event) : array
    {
        return [
            "idEvento" => $event,
            "assists" => $assists
        ];
    }

    public static function rulesAssists() : array
    {
        return [
            "idEvento" => "bail|required|numeric|exists:eventos",
            "assists" => "bail|required|array"
        ];
    }

    public static function inputRulesCancellation($gauge, $event) : array
    {
        return [
            "idEvento" => $event,
            "idMedidor" => $gauge
        ];
    }

    public static function rulesCancellation() : array
    {
        return [
            "idEvento" => "bail|required|numeric|exists:eventos",
            "idMedidor" => "bail|required|numeric|exists:medidors"
        ];
    }

    public function scopeFinalized($query, $finalized = false)
    {
        return $query->where('finalizado', $finalized);
    }

    public function scopeId($query, $id)
    {
        return $query->where('idEvento', $id);
    }

    public static function GetEvent($id)
    {
        return self::id($id)->first();
    }

    public static function _instanceAndSaving($type, $idUserEventCreate = "", $name = "", $description = "", $fine = "", $dateTime = "", $finalized = false, $idEvent = 0) : void
    {
        $event = null;
        if ($type === 'CREATE')
        {
            $event = new self();
        }
        if ($type === 'UPDATE' || $type === 'DELETE' || $type === 'MARK_COMPLETED')
        {
            $event = self::GetEvent($idEvent);
        }
        if ($type === 'UPDATE' || $type === 'CREATE')
        {
            $event->usuario_id = $idUserEventCreate;
            $event->nombreEvento = $name;
            $event->descripcionEvento = $description;
            $event->montoMulta = $fine;
            $event->fechaEvento = $dateTime;
            $event->finalizado = $finalized;
        }
        if ($type === 'MARK_COMPLETED')
        {
            $event->finalizado = $finalized;
        }
        if ($type === 'DELETE')
        {
            try
            {
                $event->delete();
            }
            catch (\Exception $e)
            {
                self::first();
            }
        }
        else
        {
            $event->save();
        }

    }

}
