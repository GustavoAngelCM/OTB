<?php

namespace App\Http\Controllers;

use App\Publicacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdController extends Controller
{
    public function createAd(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make(
            Publicacion::inputRulesAds($request->input('title'), $request->input('content'), $request->file('image')),
            Publicacion::rulesAds()
        );
        if ($validator->fails())
        {
            return response()->json([
                'success' => false,
                'message' => 'Formato incorrecto.',
                'errors' => $validator->errors()->messages()
            ], 400);
        }
        $file = $request->file('image');
        if ($file)
        {
            $dateNow = now();
            $name = "ad_{$dateNow->timestamp}_{$file->getClientOriginalName()}";
            $file->move(public_path('/ads'), $name);
            $ad = new Publicacion();
            $ad->preparingSaving($request->input('title'), $request->input('content'), $name);
            $ad->routeUrlCast();
            return response()->json([
                'success' => true,
                'message' => 'Anuncio publicado exitosamente.',
                'ad' => $ad
            ], 200);
        }
        return response()->json([
            'success' => false,
            'message' => 'Error al obtener la imagen.'
        ], 400);
    }

    public function editAd(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make(
            Publicacion::inputRulesAds($request->input('title'), $request->input('content'), $request->file('image'), $id),
            Publicacion::rulesAds($id)
        );
        if ($validator->fails())
        {
            return response()->json([
                'success' => false,
                'message' => 'Formato incorrecto.',
                'errors' => $validator->errors()->messages()
            ], 400);
        }
        $file = $request->file('image');
        $ad = Publicacion::id($id)->first();
        if ($file && $ad)
        {
            $file->move(public_path('/ads'), $ad->rutaImagen);
            $ad->titulo = $request->input('title');
            $ad->contenido = $request->input('content');
            $ad->save();
            $ad->routeUrlCast();
            return response()->json([
                'success' => true,
                'message' => 'Anuncio editado exitosamente.',
                'ad' => $ad
            ], 200);
        }
        return response()->json([
            'success' => false,
            'message' => 'Error al obtener los datos de publicación y/o imagen.'
        ], 400);
    }

    public function deleteAd($id): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make(
            Publicacion::inputRulesAds(null, null, null, $id, true),
            Publicacion::rulesAds($id, true)
        );
        if ($validator->fails())
        {
            return response()->json([
                'success' => false,
                'message' => 'Formato incorrecto.',
                'errors' => $validator->errors()->messages()
            ], 400);
        }
        $ad = Publicacion::id($id)->first();
        if ($ad)
        {
            try
            {
                $ad->delete();
                return response()->json([
                    'success' => true,
                    'message' => 'Anuncio eliminado exitosamente.',
                    'ad' => $ad
                ], 200);
            }
            catch (\Exception $e)
            {
                return response()->json([
                    'success' => false,
                    'message' => 'Error eliminar la publicación.'
                ], 400);
            }
        }
        return response()->json([
            'success' => false,
            'message' => 'Error al obtener los datos de publicación.'
        ], 400);
    }

    public function listAds(): \Illuminate\Http\JsonResponse
    {
        $adList = [];
        $ads = Publicacion::orderByDesc('idPublicacion')->get();
        foreach ($ads as $ad)
        {
            $ad->routeUrlCast();
            $adList [] = [
                'id' => $ad->idPublicacion,
                'title' => $ad->titulo,
                'content' => $ad->contenido,
                'route_image' => $ad->rutaImagen,
                'publication_date' => $ad->created_at
            ];
        }
        return response()->json([
            'success' => true,
            'message' => 'Lista de anuncios.',
            'ads' => $adList
        ], 200);
    }
}
