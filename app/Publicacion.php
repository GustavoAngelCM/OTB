<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

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
 * @method static \Illuminate\Database\Eloquent\Builder|Publicacion id($id)
 * @mixin \Eloquent
 */
class Publicacion extends Model
{
    protected  $primaryKey = 'idPublicacion';

    public static function inputRulesAds($title, $content, $img, $id = null, $delete = false): array
    {
        $inputRules = [
            'title' => $title,
            'content' => $content,
            'img' => $img
        ];
        if ($id !== null)
        {
            $inputRules = array_merge($inputRules, [
                'key_id' => (int) $id
            ]);
        }
        if ($delete)
        {
            $inputRules = [
                'key_id' => (int) $id
            ];
        }
        return $inputRules;
    }

    public static function rulesAds($id = null, $delete = false): array
    {
        $rules =  [
            'title' => 'bail|required|max:80',
            'content' => 'bail|required|max:500',
            'img' => 'bail|required|mimes:jpeg,jpg,png'
        ];
        if ($id !== null)
        {
            $rules = array_merge($rules, [
                'key_id' => 'bail|required|numeric|exists:publicacions,idPublicacion'
            ]);
        }
        if ($delete)
        {
            $rules = [
                'key_id' => 'bail|required|numeric|exists:publicacions,idPublicacion'
            ];
        }
        return $rules;
    }

    public function scopeId($query, $id)
    {
        return $query->where('idPublicacion', (int) $id);
    }

    public function routeUrlCast(): void
    {
        $this->rutaImagen =  url("/ads/{$this->rutaImagen}");
    }

    public function preparingSaving($title, $content, $image): void
    {
        $userAuth = Auth::user();
        if ($userAuth)
        {
            $this->usuario_id = $userAuth->idUsuario;
            $this->titulo = $title;
            $this->contenido = $content;
            $this->rutaImagen = $image;
            $this->fechaPublicacion = now();
            $this->save();
        }
    }
}
