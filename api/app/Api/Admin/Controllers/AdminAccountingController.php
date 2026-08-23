<?php

namespace App\Api\Admin\Controllers;

use App\Api\Admin\Actions\Accounting\ConfirmAccountingPaymentAction;
use App\Api\Admin\Actions\Accounting\ExportLedgerCsvAction;
use App\Api\Admin\Actions\Accounting\GenerateInvoicePdfAction;
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
use App\Api\V1\Repositories\OrderRepositoryInterface;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminAccountingController extends BaseApiController
{
    #[OA\Get(
        path: '/api/admin/accounting/ledger',
        summary: 'List paginated ledger entries (completed/cancelled orders as charges/refunds)',
        security: [['bearerAuth' => []]],
        tags: ['Admin Accounting'],
        parameters: [
            new OA\Parameter(
                name: 'user_id',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer'),
            ),
            new OA\Parameter(
                name: 'type',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', enum: ['charge', 'refund']),
            ),
            new OA\Parameter(
                name: 'from',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', format: 'date'),
            ),
            new OA\Parameter(
                name: 'to',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', format: 'date'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'object')),
                                new OA\Property(property: 'meta', type: 'object'),
                            ],
                            type: 'object',
                        ),
                    ],
                ),
            ),
        ],
    )]
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

    #[OA\Get(
        path: '/api/admin/accounting/invoices',
        summary: 'List paginated invoices',
        security: [['bearerAuth' => []]],
        tags: ['Admin Accounting'],
        parameters: [
            new OA\Parameter(
                name: 'search',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string'),
            ),
            new OA\Parameter(
                name: 'status',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', enum: ['paid', 'issued', 'cancelled']),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'object')),
                                new OA\Property(property: 'meta', type: 'object'),
                            ],
                            type: 'object',
                        ),
                    ],
                ),
            ),
        ],
    )]
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

    #[OA\Get(
        path: '/api/admin/accounting/invoices/{id}/pdf',
        summary: 'Download an order invoice as PDF',
        security: [['bearerAuth' => []]],
        tags: ['Admin Accounting'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'PDF file download'),
            new OA\Response(response: 404, description: 'Order not found'),
        ],
    )]
    public function downloadInvoice(int $id, OrderRepositoryInterface $orderRepository, GenerateInvoicePdfAction $action): HttpResponse
    {
        $order = $orderRepository->find($id);

        if (! $order) {
            return self::errorResponse('Замовлення не знайдено', HttpResponse::HTTP_NOT_FOUND);
        }

        return $action->execute($order)->download("invoice-{$order->order_number}.pdf");
    }

    #[OA\Get(
        path: '/api/admin/accounting/stats',
        summary: 'Get accounting statistics (revenue, refunds, net revenue)',
        security: [['bearerAuth' => []]],
        tags: ['Admin Accounting'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'totalRevenueMinor', type: 'integer', example: 100000),
                                new OA\Property(property: 'totalRefundsMinor', type: 'integer', example: 5000),
                                new OA\Property(property: 'netRevenueMinor', type: 'integer', example: 95000),
                            ],
                            type: 'object',
                        ),
                    ],
                ),
            ),
        ],
    )]
    public function accountingStats(GetAccountingStatsAction $action): JsonResponse
    {
        return self::successfulResponseWithData($action->execute());
    }

    #[OA\Get(
        path: '/api/admin/accounting/export',
        summary: 'Export the ledger (completed/cancelled orders) as CSV',
        security: [['bearerAuth' => []]],
        tags: ['Admin Accounting'],
        responses: [
            new OA\Response(response: 200, description: 'CSV file download'),
        ],
    )]
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

    #[OA\Get(
        path: '/api/admin/billing/stats',
        summary: 'Get billing statistics (revenue, active subscriptions, pending payments)',
        security: [['bearerAuth' => []]],
        tags: ['Admin Accounting'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'revenueMinor', type: 'integer', example: 100000),
                                new OA\Property(property: 'activeSubscriptions', type: 'integer', example: 0),
                                new OA\Property(property: 'pendingPaymentsCount', type: 'integer', example: 2),
                            ],
                            type: 'object',
                        ),
                    ],
                ),
            ),
        ],
    )]
    public function billingStats(GetBillingStatsAction $action): JsonResponse
    {
        return self::successfulResponseWithData($action->execute());
    }

    #[OA\Get(
        path: '/api/admin/billing/payments/pending',
        summary: 'List paginated pending payments (orders awaiting payment)',
        security: [['bearerAuth' => []]],
        tags: ['Admin Accounting'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'object')),
                                new OA\Property(property: 'meta', type: 'object'),
                            ],
                            type: 'object',
                        ),
                    ],
                ),
            ),
        ],
    )]
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

    #[OA\Post(
        path: '/api/admin/billing/payments/{id}/confirm',
        summary: 'Approve or reject a pending payment',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['approve'],
                properties: [
                    new OA\Property(property: 'approve', type: 'boolean', example: true),
                ],
            ),
        ),
        tags: ['Admin Accounting'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Payment approved or rejected'),
            new OA\Response(response: 404, description: 'Order not found'),
        ],
    )]
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

    #[OA\Get(
        path: '/api/admin/billing/payments/{id}/proof',
        summary: 'View payment proof for an order',
        security: [['bearerAuth' => []]],
        tags: ['Admin Accounting'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'proofUrl', type: 'string', example: null, nullable: true),
                                new OA\Property(property: 'note', type: 'string', example: 'No payment proof uploaded for this order.'),
                            ],
                            type: 'object',
                        ),
                    ],
                ),
            ),
        ],
    )]
    public function viewProof(int $id): JsonResponse
    {
        return self::successfulResponseWithData([
            'id' => $id,
            'proofUrl' => null,
            'note' => 'No payment proof uploaded for this order.',
        ]);
    }

    #[OA\Get(
        path: '/api/admin/billing/subscriptions',
        summary: 'List billing subscriptions (not yet implemented, always empty)',
        security: [['bearerAuth' => []]],
        tags: ['Admin Accounting'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'object')),
                                new OA\Property(property: 'meta', type: 'object'),
                            ],
                            type: 'object',
                        ),
                    ],
                ),
            ),
        ],
    )]
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
