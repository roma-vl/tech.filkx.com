<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Actions\Support\CreateSupportTicketAction;
use App\Api\V1\Actions\Support\GetSupportTicketAction;
use App\Api\V1\Actions\Support\ListMySupportTicketsAction;
use App\Api\V1\Actions\Support\MarkSupportTicketAsReadAction;
use App\Api\V1\Actions\Support\SendSupportMessageAction;
use App\Api\V1\Actions\Support\TransferSupportTicketAction;
use App\Api\V1\Requests\SendSupportMessageRequest;
use App\Api\V1\Requests\StoreSupportTicketRequest;
use App\Api\V1\Resources\Support\SupportMessageResource;
use App\Api\V1\Resources\Support\SupportTicketResource;
use App\Models\SupportTicket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class SupportController extends BaseApiController
{
    #[OA\Get(
        path: '/api/support/tickets',
        summary: "List the authenticated user's support tickets",
        security: [['bearerAuth' => []]],
        tags: ['Support'],
        responses: [
            new OA\Response(
                response: 200,
                description: "The authenticated user's support tickets, newest first",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/SupportTicketResource'),
                        ),
                    ],
                ),
            ),
        ],
    )]
    public function index(Request $request, ListMySupportTicketsAction $action): JsonResponse
    {
        return self::successfulResponseWithData(
            SupportTicketResource::collection($action->execute($request->user()))
        );
    }

    #[OA\Post(
        path: '/api/support/tickets',
        summary: 'Open a new support ticket',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['subject', 'message'],
                    properties: [
                        new OA\Property(property: 'subject', type: 'string', maxLength: 255),
                        new OA\Property(property: 'message', type: 'string'),
                        new OA\Property(property: 'file', description: 'Up to 10 MB', type: 'string', format: 'binary'),
                        new OA\Property(property: 'product_id', description: 'The product this ticket is about, if opened from a product page', type: 'integer', nullable: true),
                    ],
                ),
            ),
        ),
        tags: ['Support'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'The created ticket with its first message',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/SupportTicketResource'),
                    ],
                ),
            ),
            new OA\Response(response: 422, description: 'Validation failed'),
        ],
    )]
    public function store(StoreSupportTicketRequest $request, CreateSupportTicketAction $action): JsonResponse
    {
        $ticket = $action->execute($request->user(), $request->validated(), $request->file('file'));

        return self::successfulResponseWithData(new SupportTicketResource($ticket));
    }

    #[OA\Get(
        path: '/api/support/tickets/{ticket}',
        summary: 'Get a support ticket with its public messages',
        security: [['bearerAuth' => []]],
        tags: ['Support'],
        parameters: [
            new OA\Parameter(name: 'ticket', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'The support ticket',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/SupportTicketResource'),
                    ],
                ),
            ),
            new OA\Response(response: 403, description: 'The ticket does not belong to the authenticated user'),
        ],
    )]
    public function show(Request $request, SupportTicket $ticket, GetSupportTicketAction $action): JsonResponse
    {
        try {
            $ticket = $action->execute($ticket, $request->user());
        } catch (AccessDeniedHttpException $e) {
            return self::errorResponse($e->getMessage(), Response::HTTP_FORBIDDEN);
        }

        return self::successfulResponseWithData(new SupportTicketResource($ticket));
    }

    #[OA\Post(
        path: '/api/support/tickets/{ticket}/mark-as-read',
        summary: "Mark a ticket's admin messages as read by the customer",
        security: [['bearerAuth' => []]],
        tags: ['Support'],
        parameters: [
            new OA\Parameter(name: 'ticket', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Marked as read'),
            new OA\Response(response: 403, description: 'The ticket does not belong to the authenticated user'),
        ],
    )]
    public function markAsRead(Request $request, SupportTicket $ticket, MarkSupportTicketAsReadAction $action): Response
    {
        $action->execute($ticket, $request->user());

        return response()->noContent();
    }

    #[OA\Post(
        path: '/api/support/tickets/{ticket}/message',
        summary: 'Send a message on a support ticket',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', nullable: true),
                        new OA\Property(property: 'file', description: 'Up to 10 MB', type: 'string', format: 'binary'),
                    ],
                ),
            ),
        ),
        tags: ['Support'],
        parameters: [
            new OA\Parameter(name: 'ticket', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'The created message',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/SupportMessageResource'),
                    ],
                ),
            ),
            new OA\Response(response: 403, description: 'The ticket does not belong to the authenticated user'),
            new OA\Response(response: 422, description: 'Neither a message nor a file was provided'),
        ],
    )]
    public function sendMessage(SendSupportMessageRequest $request, SupportTicket $ticket, SendSupportMessageAction $action): JsonResponse
    {
        try {
            $message = $action->execute($ticket, $request->user(), $request->validated(), $request->file('file'));
        } catch (AccessDeniedHttpException $e) {
            return self::errorResponse($e->getMessage(), Response::HTTP_FORBIDDEN);
        } catch (UnprocessableEntityHttpException $e) {
            return self::errorResponse($e->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return self::successfulResponseWithData(new SupportMessageResource($message));
    }

    #[OA\Post(
        path: '/api/support/tickets/{ticket}/transfer',
        summary: 'Hand a ticket back to a human agent',
        security: [['bearerAuth' => []]],
        tags: ['Support'],
        parameters: [
            new OA\Parameter(name: 'ticket', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'The updated ticket',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/SupportTicketResource'),
                    ],
                ),
            ),
            new OA\Response(response: 403, description: 'The ticket does not belong to the authenticated user'),
        ],
    )]
    public function transfer(Request $request, SupportTicket $ticket, TransferSupportTicketAction $action): JsonResponse
    {
        try {
            $ticket = $action->execute($ticket, $request->user(), 'human');
        } catch (AccessDeniedHttpException $e) {
            return self::errorResponse($e->getMessage(), Response::HTTP_FORBIDDEN);
        }

        return self::successfulResponseWithData(new SupportTicketResource($ticket));
    }

    #[OA\Post(
        path: '/api/support/tickets/{ticket}/transfer-to-ai',
        summary: 'Hand a ticket over to the AI assistant',
        security: [['bearerAuth' => []]],
        tags: ['Support'],
        parameters: [
            new OA\Parameter(name: 'ticket', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'The updated ticket',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/SupportTicketResource'),
                    ],
                ),
            ),
            new OA\Response(response: 403, description: 'The ticket does not belong to the authenticated user'),
        ],
    )]
    public function transferToAi(Request $request, SupportTicket $ticket, TransferSupportTicketAction $action): JsonResponse
    {
        try {
            $ticket = $action->execute($ticket, $request->user(), 'ai');
        } catch (AccessDeniedHttpException $e) {
            return self::errorResponse($e->getMessage(), Response::HTTP_FORBIDDEN);
        }

        return self::successfulResponseWithData(new SupportTicketResource($ticket));
    }
}
