<?php

namespace App\Api\Admin\Controllers;

use App\Api\Admin\Actions\Accounting\ConfirmAccountingPaymentAction;
use App\Api\Admin\Actions\Accounting\ExportLedgerCsvAction;
use App\Api\Admin\Actions\Accounting\GetAccountingStatsAction;
use App\Api\Admin\Actions\Accounting\GetBillingStatsAction;
use App\Api\Admin\Actions\Accounting\GetInvoicesAction;
use App\Api\Admin\Actions\Accounting\GetLedgerAction;
use App\Api\Admin\Actions\Accounting\GetPendingPaymentsAction;
use App\Api\Admin\Dto\InvoiceFilterDto;
use App\Api\Admin\Dto\LedgerFilterDto;
use App\Api\Admin\Requests\ConfirmPaymentRequest;
use App\Api\Admin\Requests\InvoiceFilterRequest;
use App\Api\Admin\Requests\LedgerFilterRequest;
use App\Api\Admin\Resources\InvoiceResource;
use App\Api\Admin\Resources\LedgerResource;
use App\Api\Admin\Resources\PendingPaymentResource;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminAccountingController extends BaseApiController
{
    public function ledger(LedgerFilterRequest $request, GetLedgerAction $action): JsonResponse
    {
        $paginator = $action->execute(LedgerFilterDto::fromRequest($request), self::PER_PAGE);

        return self::successfulResponseWithData([
            'data' => LedgerResource::collection($paginator->items()),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    public function invoices(InvoiceFilterRequest $request, GetInvoicesAction $action): JsonResponse
    {
        $paginator = $action->execute(InvoiceFilterDto::fromRequest($request), self::PER_PAGE);

        return self::successfulResponseWithData([
            'data' => InvoiceResource::collection($paginator->items()),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    public function accountingStats(GetAccountingStatsAction $action): JsonResponse
    {
        return self::successfulResponseWithData($action->execute());
    }

    public function export(ExportLedgerCsvAction $action): StreamedResponse
    {
        $orders = $action->execute();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="ledger_export.csv"',
        ];

        $callback = function () use ($orders) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'User', 'Type', 'Amount', 'Currency', 'Reference', 'Date']);

            foreach ($orders as $order) {
                $isCompleted = $order->status === 'completed';
                fputcsv($file, [
                    $order->id,
                    $order->user ? $order->user->name : $order->customer_name,
                    $isCompleted ? 'charge' : 'refund',
                    $isCompleted ? $order->total_price : -$order->total_price,
                    'UAH',
                    'order',
                    $order->created_at->toIso8601String(),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function billingStats(GetBillingStatsAction $action): JsonResponse
    {
        return self::successfulResponseWithData($action->execute());
    }

    public function pendingPayments(GetPendingPaymentsAction $action): JsonResponse
    {
        $paginator = $action->execute(self::PER_PAGE);

        return self::successfulResponseWithData([
            'data' => PendingPaymentResource::collection($paginator->items()),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => self::PER_PAGE,
                'total' => $paginator->total(),
            ],
        ]);
    }

    public function confirmPayment(ConfirmPaymentRequest $request, int $id, ConfirmAccountingPaymentAction $action): JsonResponse
    {
        $approve = (bool) $request->input('approve');

        $action->execute($id, $approve);

        if ($approve) {
            return self::successfulResponse('Payment approved and order completed.');
        } else {
            return self::successfulResponse('Payment rejected and order cancelled.');
        }
    }

    public function viewProof(int $id): JsonResponse
    {
        return self::successfulResponseWithData([
            'id' => $id,
            'proofUrl' => null,
            'note' => 'No payment proof uploaded for this order.',
        ]);
    }

    public function subscriptions(): JsonResponse
    {
        return self::successfulResponseWithData([
            'data' => [],
            'meta' => [
                'current_page' => 1,
                'last_page' => 1,
                'per_page' => self::PER_PAGE,
                'total' => 0,
            ],
        ]);
    }
}
