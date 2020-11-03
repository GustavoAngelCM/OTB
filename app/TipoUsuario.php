<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\TipoUsuario
 *
 * @property int $idTipoUsuario
 * @property string $nombreTipoUsuario
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|TipoUsuario newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TipoUsuario newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TipoUsuario query()
 * @method static \Illuminate\Database\Eloquent\Builder|TipoUsuario whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TipoUsuario whereIdTipoUsuario($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TipoUsuario whereNombreTipoUsuario($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TipoUsuario whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class TipoUsuario extends Model
{
    protected  $primaryKey = 'idTipoUsuario';
}
