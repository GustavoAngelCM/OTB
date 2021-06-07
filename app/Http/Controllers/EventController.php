<?php

namespace App\Http\Controllers;

use App\Asistencia;
use App\Cancelacion;
use App\Evento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Webpatser\Uuid\Uuid;

class EventController extends Controller
{
    public function createEvent(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        if ($user && $user->tipoUsuario_id === 1)
        {
            $validator = Validator::make(
                Evento::inputRulesEvents($request->input('motivoReunion'), $request->input('mensaje'), $request->input('multa'), $request->input('fechaHoraReunion')),
                Evento::rulesEvents()
            );
            $errors = $validator->errors();
            if ($validator->fails())
            {
                return response()->json([
                    'success' => false,
                    'message' => 'Formato incorrecto.',
                    'errors' => $errors->messages(),
                ], 400);
            }
            try
            {
                Evento::_instanceAndSaving('CREATE', $user->idUsuario, $request->input('motivoReunion'), $request->input('mensaje'), $request->input('multa'), $request->input('fechaHoraReunion'));
                return response()->json([
                    'success' => true,
                    'message' => 'Evento creado exitosamente.',
                ], 200);
            }
            catch (\Exception $e)
            {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al completar transacción.'
                ], 400);
            }
        }
        return response()->json([
            'success' => false,
            'message' => 'Credenciales insuficientes.',
        ], 401);
    }

    public function updateEvent(Request $request, $id)
    {
        $user = Auth::user();
        if ($user && $user->tipoUsuario_id === 1)
        {
            $validator = Validator::make(
                Evento::inputRulesEvents($request->input('motivoReunion'), $request->input('mensaje'), $request->input('multa'), $request->input('fechaHoraReunion'), true, (int)$id),
                Evento::rulesEvents(true)
            );
            $errors = $validator->errors();
            if ($validator->fails())
            {
                return response()->json([
                    'success' => false,
                    'message' => 'Formato incorrecto.',
                    'errors' => $errors->messages(),
                ], 400);
            }
            $finalizedEvent = Evento::id($id)->finalized()->first();
            if ($finalizedEvent)
            {
                try
                {
                    Evento::_instanceAndSaving('UPDATE', $user->idUsuario, $request->input('motivoReunion'), $request->input('mensaje'), $request->input('multa'), $request->input('fechaHoraReunion'), false, $id);
                    return response()->json([
                        'success' => true,
                        'message' => 'Evento actualizado exitosamente.',
                    ], 200);
                }
                catch (\Exception $e)
                {
                    return response()->json([
                        'success' => false,
                        'message' => 'Error al completar transacción.'
                    ], 400);
                }
            }
            return response()->json([
                'success' => false,
                'message' => 'El evento ya ha finalizado.'
            ], 400);
        }
        return response()->json([
            'success' => false,
            'message' => 'Credenciales insuficientes.',
        ], 401);
    }

    public function deleteEvent($id): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        if ($user->tipoUsuario_id === 1)
        {
            $validator = Validator::make(
                Evento::inputRulesDeleteEvents($id),
                Evento::rulesDeleteEvents()
            );
            $errors = $validator->errors();
            if ($validator->fails())
            {
                return response()->json([
                    'success' => false,
                    'message' => 'Formato incorrecto.',
                    'errors' => $errors->messages(),
                ], 400);
            }
            $finalizedEvent = Evento::id($id)->finalized()->first();
            if ($finalizedEvent)
            {
                try
                {
                    Evento::_instanceAndSaving('DELETE', null, null, null, null, null, null, $id);
                    return response()->json([
                        'success' => true,
                        'message' => 'Evento eliminado exitosamente.',
                    ], 200);
                }
                catch (\Exception $e)
                {
                    return response()->json([
                        'success' => false,
                        'message' => 'Error al completar transacción.'
                    ], 400);
                }
            }
            return response()->json([
                'success' => false,
                'message' => 'Ele evento ya ha finalizado.'
            ], 400);
        }
        return response()->json([
            'success' => false,
            'message' => 'Credenciales insuficientes.',
        ], 401);
    }

    public function assistsEvent(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        if ( $user && $user->tipoUsuario_id === 1)
        {
            $validator = Validator::make(
                Evento::inputRulesAssists($request->input('assists'), $id),
                Evento::rulesAssists()
            );
            $errors = $validator->errors();
            if ($validator->fails())
            {
                return response()->json([
                    'success' => false,
                    'message' => 'Formato incorrecto.',
                    'errors' => $errors->messages(),
                ], 400);
            }
            $existError = false;
            foreach ($request->input('assists') as $assistance)
            {
                $validator = Validator::make(
                    Evento::inputRulesEventAssistance($assistance['gauge'], $assistance['attended']),
                    Evento::rulesEventAssistance()
                );
                if ($validator->fails())
                {
                    $existError = true;
                }
            }
            if ($existError)
            {
                return response()->json([
                    'success' => false,
                    'message' => 'Formato incorrecto.',
                    'errors' => $validator->errors()->messages(),
                ], 400);
            }
            $finalizedEvent = Evento::id($id)->finalized()->first();
            if ($finalizedEvent)
            {
                try
                {
                    foreach ($request->input('assists') as $assistance)
                    {
                        Asistencia::_instanceAndSaving($assistance['gauge'], $id, $assistance['attended'], now());
                    }
                    Evento::_instanceAndSaving('MARK_COMPLETED', null, null, null, null, null, true, $id);
                    return response()->json([
                        'success' => true,
                        'message' => 'Asistencias marcadas correctamente.',
                    ], 200);
                }
                catch (\Exception $e)
                {
                    return response()->json([
                        'success' => false,
                        'message' => 'Error al marcar las asistencias.',
                        'errors' => $validator->errors()->messages(),
                    ], 400);
                }
            }
            return response()->json([
                'success' => false,
                'message' => 'Ele evento ya ha finalizado.'
            ], 400);
        }
        return response()->json([
            'success' => false,
            'message' => 'Credenciales insuficientes.',
        ], 401);
    }

    public function cancellationRecord(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        if ( $user && $user->tipoUsuario_id === 1)
        {
            $validator = Validator::make(
                Evento::inputRulesCancellation($request->input('gauge'), $request->input('event')),
                Evento::rulesCancellation()
            );
            $errors = $validator->errors();
            if ($validator->fails())
            {
                return response()->json([
                    'success' => false,
                    'message' => 'Formato incorrecto.',
                    'errors' => $errors->messages(),
                ], 400);
            }
            try
            {
                $assistance = Asistencia::eventRelation($request->input('event'))->gaugeRelation($request->input('gauge'))->isNotCancelled()->theyAttend(false)->first();
                if ($assistance)
                {
                    $event = Evento::GetEvent($request->input('event'));
                    $key = Uuid::generate()->string;
                    Cancelacion::_instanceAndSaving($event->montoMulta, $key, 'BOLIVIANOS', 'EFECTIVO');
                    $assistance->cancelacion_id = Cancelacion::cancellationKey($key)->first()->idCancelacion;
                    $assistance->save();
                    return response()->json([
                        'success' => true,
                        'message' => 'Multa pagada existosamente.'
                    ], 200);
                }
                return response()->json([
                    'success' => false,
                    'message' => 'Error no se pudo encontrar el registro de asistencia.'
                ], 400);
            }
            catch (\Exception $e)
            {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al cancelar la multa por inasistencia.'
                ], 400);
            }
        }
        return response()->json([
            'success' => false,
            'message' => 'Credenciales insuficientes.',
        ], 401);
    }

    public function getListAssists($event, $type)
    {
        $user = Auth::user();
        if ($user && $user->tipoUsuario_id === 1)
        {
            $validator = Validator::make(
                Evento::inputRulesGetEventAssistance((int)$event, $type),
                Evento::rulesGetEventAssistance()
            );
            $errors = $validator->errors();
            if ($validator->fails())
            {
                return response()->json([
                    'success' => false,
                    'message' => 'Formato incorrecto.',
                    'errors' => $errors->messages(),
                ], 400);
            }
            try
            {
                $gaugesAssists = [];
                switch ($type)
                {
                    case 'ALL':
                        $gaugesAssists = Asistencia::eventRelation($event)->get();
                        break;
                    case 'PAYMENT':
                        $gaugesAssists = Asistencia::eventRelation($event)->isCancelled()->get();
                        break;
                    case 'NOT_PAYMENT':
                        $gaugesAssists = Asistencia::eventRelation($event)->theyAttend(false)->isNotCancelled()->get();
                        break;
                    case 'THEY_ATTEND':
                        $gaugesAssists = Asistencia::eventRelation($event)->theyAttend()->get();
                        break;
                    case 'THEY_DID_NOT_ATTEND':
                        $gaugesAssists = Asistencia::eventRelation($event)->theyAttend(false)->get();
                        break;
                }
                $eventList = [];
                foreach ($gaugesAssists as $gaugeAssist)
                {
                    $relatedAttended = Asistencia::eventRelation($gaugeAssist->evento_id)->gaugeRelation($gaugeAssist->medidor_id)->assistance($gaugeAssist->idAsistencia)->with(['gauge.user.person', 'event', 'payment'])->first();
                    if ($relatedAttended)
                    {
                        $eventList [] = [
                            'fullName' => $relatedAttended->gauge->user->person->fullName(),
                            'order' => $relatedAttended->gauge->ordenMedidor,
                            'id' => $relatedAttended->gauge->idMedidor,
                            'number' => $relatedAttended->gauge->numeroMedidor,
                            'attended' => $relatedAttended->asistio,
                            'cancelled' => $relatedAttended->payment->keyCancelacion ?? null,
                            'nameEvent' => $relatedAttended->event->nombreEvento,
                            'finalized' => $relatedAttended->event->finalizado,
                            'dateEvent' => $relatedAttended->event->fechaEvento
                        ];
                    }
                }
                return response()->json([
                    'success' => true,
                    'message' => $type,
                    'data' => $eventList
                ], 200);
            }
            catch (\Exception $e)
            {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al listar los medidores.',
                    'errors' => $e
                ], 400);
            }
        }
        return response()->json([
            'success' => false,
            'message' => 'Credenciales insuficientes.',
        ], 401);
    }

    public function events(): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Lista de eventos',
            'data' => Evento::select([
                'idEvento as id',
                'nombreEvento as name',
                'descripcionEvento as description',
                'montoMulta as fine',
                'finalizado as finalized',
                'fechaEvento as dateTime'
            ])->orderByDesc('idEvento')->get()
        ], 200);
    }
}
