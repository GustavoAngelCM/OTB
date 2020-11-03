<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Telefono
 *
 * @property int $idTelefono
 * @property int $persona_id
 * @property int $numeroTelefono
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Telefono newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Telefono newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Telefono query()
 * @method static \Illuminate\Database\Eloquent\Builder|Telefono whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Telefono whereIdTelefono($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Telefono whereNumeroTelefono($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Telefono wherePersonaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Telefono whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Telefono extends Model
{
    protected  $primaryKey = 'idTelefono';

    public static function inputRulesPhone($phoneNumber): array
    {
        return [
            'numeroTelefono' => $phoneNumber,
        ];
    }

    public static function rulesPhone(): array
    {
        return [
            'numeroTelefono' => 'bail|required|unique:telefonos',
        ];
    }

    public function preparingSaving($person, $numberPhone): void
    {
        $this->persona_id = $person;
        $this->numeroTelefono = $numberPhone;
        $this->save();
    }

}
