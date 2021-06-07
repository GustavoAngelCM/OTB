<?php

namespace App\Http\Controllers;

use App\Cancelacion;
use App\HistorialTransferencia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
{
    public function getTransactions(): \Illuminate\Http\JsonResponse
    {
        $transactions = [];
        $meterTransactions = HistorialTransferencia::with(['previousUser.person', 'currentUser.person', 'cancellation', 'gauge'])->orderByDesc('updated_at')->get();
        foreach ($meterTransactions as $meterTransaction)
        {
            $transactions [] = [
                'id' => $meterTransaction->idHistorialTransferencias,
                'total' => $meterTransaction->montoTotalTransferencia,
                'amount_paid' => $meterTransaction->montoCancelado,
                'transfer_status' => $meterTransaction->estadoTransferencia,
                'transaction_date' => $meterTransaction->fechaHoraTransaferencia,
                'previous_user' => HistorialTransferencia::userData($meterTransaction->previousUser),
                'current_user' => HistorialTransferencia::userData($meterTransaction->currentUser),
                'cancellation' => HistorialTransferencia::cancellationData($meterTransaction->cancellation),
                'gauge' => HistorialTransferencia::gaugeData($meterTransaction->gauge)
            ];
        }
        return response()->json([
            'success' => true,
            'message' => 'Transacciones de medidores',
            'transactions' => $transactions
        ], 200);
    }

    public function paymentTransaction(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make(
            HistorialTransferencia::inputRulesPayment($request->input('transaction'), $request->input('cancellation'), $request->input('mount')),
            HistorialTransferencia::rulesPayment()
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
        $transaction = HistorialTransferencia::id($request->input('transaction'))->first();
        $cancellation = Cancelacion::id($request->input('cancellation'))->first();
        if ($transaction && $cancellation)
        {
            $completedWithAmountExceeded = $transaction->montoTotalTransferencia < ($transaction->montoCancelado + $request->input('mount'));
            if ($transaction->montoTotalTransferencia > ($transaction->montoCancelado + $request->input('mount')))
            {
                $transaction->estadoTransferencia = 'IN_PROCESS';
                $transaction->montoCancelado += $request->input('mount');
                $cancellation->montoCancelacion = $transaction->montoCancelado;
            }
            else
            {
                $transaction->estadoTransferencia = 'COMPLETED';
                $cancellation->montoCancelacion = $transaction->montoCancelado + $request->input('mount');
                $transaction->montoCancelado = $transaction->montoTotalTransferencia;
            }
            $transaction->fechaHoraTransaferencia = now();
            $cancellation->fechaCancelacion = now();
            $transaction->save();
            $cancellation->save();
            return response()->json([
                'success' => true,
                'message' => !$completedWithAmountExceeded ? 'Pago completado exitosamente.' : 'Se completó el pago, más el monto de transferencia fue exedido.'
            ], !$completedWithAmountExceeded ? 200 : 206);
        }
        return response()->json([
            'success' => false,
            'message' => 'Error al obtener la transacción y/o cancelación.'
        ], 400);
    }
}
