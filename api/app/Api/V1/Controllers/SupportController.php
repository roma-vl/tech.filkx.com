<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Actions\Support\CreateSupportTicketAction;
use App\Api\V1\Actions\Support\GetSupportTicketsAction;
use App\Api\V1\Actions\Support\MarkSupportTicketReadAction;
use App\Api\V1\Actions\Support\ReplySupportTicketAction;
use App\Api\V1\Actions\Support\TransferToAiAction;
use App\Api\V1\Actions\Support\TransferToHumanAction;
use App\Api\V1\Requests\Support\ReplySupportTicketRequest;
use App\Api\V1\Requests\Support\StoreSupportTicketRequest;
use App\Api\V1\Resources\Support\SupportMessageResource;
use App\Api\V1\Resources\Support\SupportTicketResource;
use App\Models\SupportTicket;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use OpenApi\Annotations as OA;

class SupportController extends BaseApiController
{
    use AuthorizesRequests;

    /**
     * @OA\Get(
     *     path="/api/support/tickets",
     *     summary="List support tickets",
     *     tags={"Support"},
     *     security={{"passport": {}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="User tickets",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/SupportTicketResource"))
     *         )
     *     )
     * )
     */
    public function index(Request $request, GetSupportTicketsAction $action): JsonResponse
    {
        $tickets = $action->execute($request->user());

        return self::successfulResponseWithData(SupportTicketResource::collection($tickets));
    }

    /**
     * @OA\Post(
     *     path="/api/support/tickets",
     *     summary="Create support ticket",
     *     tags={"Support"},
     *     security={{"passport": {}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"subject", "message"},
     *
     *             @OA\Property(property="subject", type="string"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Ticket created",
     *
     *         @OA\JsonContent(ref="#/components/schemas/SupportTicketResource")
     *     )
     * )
     */
    public function store(StoreSupportTicketRequest $request, CreateSupportTicketAction $action): JsonResponse
    {
        $ticket = $action->execute(
            user: $request->user(),
            subject: $request->validated('subject'),
            message: $request->validated('message'),
            file: $request->file('file')
        );

        return self::successfulResponseWithData(
            new SupportTicketResource($ticket->load('messages')),
        );
    }

    /**
     * @OA\Get(
     *     path="/api/support/tickets/{id}",
     *     summary="Get ticket details",
     *     tags={"Support"},
     *     security={{"passport": {}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Ticket details",
     *
     *         @OA\JsonContent(ref="#/components/schemas/SupportTicketResource")
     *     )
     * )
     */
    public function show(SupportTicket $ticket): JsonResponse
    {
        $this->authorize('view', $ticket);

        return self::successfulResponseWithData(
            new SupportTicketResource($ticket->load(['publicMessages.user', 'user']))
        );
    }

    /**
     * @OA\Patch(
     *     path="/api/support/tickets/{id}/read",
     *     summary="Mark ticket as read",
     *     tags={"Support"},
     *     security={{"passport": {}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\Response(response=204, description="Marked as read")
     * )
     */
    public function markAsRead(SupportTicket $ticket, MarkSupportTicketReadAction $action): Response
    {
        $this->authorize('update', $ticket);

        $action->execute($ticket);

        return response()->noContent();
    }

    /**
     * @OA\Post(
     *     path="/api/support/tickets/{id}/message",
     *     summary="Reply to ticket",
     *     tags={"Support"},
     *     security={{"passport": {}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *
     *             @OA\Schema(
     *
     *                 @OA\Property(property="message", type="string"),
     *                 @OA\Property(property="file", type="string", format="binary")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Message sent",
     *
     *         @OA\JsonContent(ref="#/components/schemas/SupportMessageResource")
     *     )
     * )
     */
    public function sendMessage(
        ReplySupportTicketRequest $request,
        SupportTicket $ticket,
        ReplySupportTicketAction $action
    ): JsonResponse {
        $this->authorize('update', $ticket);

        $message = $action->execute(
            user: $request->user(),
            ticket: $ticket,
            message: $request->validated('message'),
            file: $request->file('file')
        );

        return self::successfulResponseWithData(new SupportMessageResource($message));
    }

    public function transfer(SupportTicket $ticket, TransferToHumanAction $action): JsonResponse
    {
        $this->authorize('update', $ticket);

        $ticket = $action->execute($ticket);

        return self::successfulResponseWithData(new SupportTicketResource($ticket->load('messages')));
    }

    public function transferToAi(SupportTicket $ticket, TransferToAiAction $action): JsonResponse
    {
        $this->authorize('update', $ticket);

        $ticket = $action->execute($ticket);

        return self::successfulResponseWithData(new SupportTicketResource($ticket->load('messages')));
    }
}
