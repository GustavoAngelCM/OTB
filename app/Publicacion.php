<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Publicacion
 *
 * @property int $idPublicacion
 * @property int $usuario_id
 * @property string $titulo
 * @property string $contenido
 * @property string $rutaImagen
 * @property string $fechaPublicacion
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Publicacion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Publicacion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Publicacion query()
 * @method static \Illuminate\Database\Eloquent\Builder|Publicacion whereContenido($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Publicacion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Publicacion whereFechaPublicacion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Publicacion whereIdPublicacion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Publicacion whereRutaImagen($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Publicacion whereTitulo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Publicacion whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Publicacion whereUsuarioId($value)
 * @mixin \Eloquent
 */
class Publicacion extends Model
{
    protected  $primaryKey = 'idPublicacion';
}
