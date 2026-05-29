<?php

namespace App\Api\Admin\Controllers;

use App\Api\Admin\Actions\Support\GetSupportStatsAction;
use App\Api\Admin\Actions\Support\ListSupportTicketMessagesAction;
use App\Api\Admin\Actions\Support\ListSupportTicketsAction;
use App\Api\Admin\Actions\Support\MarkSupportMessagesReadAction;
use App\Api\Admin\Actions\Support\MarkSupportTicketAsReadAction;
use App\Api\Admin\Actions\Support\ReplySupportTicketAction;
use App\Api\Admin\Actions\Support\UpdateSupportTicketStatusAction;
use App\Api\Admin\Actions\Support\UpdateSupportTicketTagsAction;
use App\Api\Admin\Requests\Support\ReplySupportTicketRequest;
use App\Api\Admin\Requests\Support\UpdateSupportTicketStatusRequest;
use App\Api\Admin\Requests\Support\UpdateSupportTicketTagsRequest;
use App\Api\Admin\Resources\AdminSupportMessageResource;
use App\Api\Admin\Resources\AdminSupportTicketResource;
use App\Api\V1\Actions\Support\TransferToAiAction;
use App\Api\V1\Enum\SupportStatusEnum;
use App\Models\SupportTicket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminSupportController extends BaseApiController
{
    /**
     * @OA\Get(
     *     path="/api/admin/support/tickets",
     *     summary="List all support tickets",
     *     tags={"Admin Support"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(name="status", in="query", @OA\Schema(type="string"), example="new"),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/SupportTicket"))
     *         )
     *     )
     * )
     */
    public function index(Request $request, ListSupportTicketsAction $action): JsonResponse
    {
        $tickets = $action->execute($request->only(['status', 'search', 'tag']));

        return self::successfulResponseWithData(AdminSupportTicketResource::collection($tickets));
    }

    /**
     * @OA\Get(
     *     path="/api/admin/support/tickets/{id}",
     *     summary="Get support ticket details",
     *     tags={"Admin Support"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/SupportTicket")
     *     )
     * )
     */
    public function show(SupportTicket $ticket): JsonResponse
    {
        $ticket->load(['user']);
        
        // Load latest 5 messages for initial view (descending by ID/created_at)
        // Then reverse to show in chronological order
        $messages = $ticket->messages()->with('user')->latest()->take(5)->get()->reverse()->values();
        
        $ticket->setRelation('messages', $messages);

        return self::successfulResponseWithData(
            new AdminSupportTicketResource($ticket)
        );
    }

    /**
     * @OA\Post(
     *     path="/api/admin/support/tickets/{id}/mark-as-read",
     *     summary="Mark ticket as read",
     *     tags={"Admin Support"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\Response(response=204, description="No content")
     * )
     */
    public function markAsRead(SupportTicket $ticket, MarkSupportTicketAsReadAction $action): JsonResponse
    {
        $action->execute($ticket);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @OA\Patch(
     *     path="/api/admin/support/tickets/{id}/status",
     *     summary="Update support ticket status",
     *     tags={"Admin Support"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="status", type="string", enum={"new", "accepted", "resolved", "closed"}, example="resolved")
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="Status updated")
     * )
     */
    public function updateStatus(
        UpdateSupportTicketStatusRequest $request,
        SupportTicket $ticket,
        UpdateSupportTicketStatusAction $action
    ): JsonResponse {
        $updated = $action->execute($ticket, $request->input('status'));

        return self::successfulResponseWithData(new AdminSupportTicketResource($updated));
    }

    /**
     * @OA\Post(
     *     path="/api/admin/support/tickets/{id}/reply",
     *     summary="Reply to a support ticket",
     *     tags={"Admin Support"},
     *     security={{"bearerAuth":{}}},
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
     *                 @OA\Property(property="message", type="string", example="How can I help you?"),
     *                 @OA\Property(property="file", type="string", format="binary")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="Reply sent")
     * )
     */
    public function reply(
        ReplySupportTicketRequest $request,
        SupportTicket $ticket,
        ReplySupportTicketAction $action
    ): JsonResponse {
        $message = $action->execute(
            $ticket,
            $request->validated(),
            $request->file('file')
        );

        return self::successfulResponseWithData(new AdminSupportMessageResource($message));
    }

    /**
     * @OA\Get(
     *     path="/api/admin/support/stats",
     *     summary="Get support statistics",
     *     tags={"Admin Support"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="totalTickets", type="integer", example=10),
     *                 @OA\Property(property="resolvedTickets", type="integer", example=5),
     *                 @OA\Property(property="chartData", type="array", @OA\Items(type="object"))
     *             )
     *         )
     *     )
     * )
     */
    public function stats(GetSupportStatsAction $action): JsonResponse
    {
        $stats = $action->execute();

        return self::successfulResponseWithData($stats);
    }

    /**
     * @OA\Delete(
     *     path="/api/admin/support/tickets/{id}",
     *     summary="Delete (soft-delete) a support ticket",
     *     tags={"Admin Support"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\Response(response=204, description="No content")
     * )
     */
    public function destroy(SupportTicket $ticket, UpdateSupportTicketStatusAction $action): JsonResponse
    {
        $action->execute($ticket, SupportStatusEnum::DELETED->value);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @OA\Patch(
     *     path="/api/admin/support/tickets/{id}/tags",
     *     summary="Update ticket tags",
     *     tags={"Admin Support"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="tags", type="array", @OA\Items(type="string"), example={"bug", "billing"})
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/SupportTicket")
     *     )
     * )
     */
    public function updateTags(
        UpdateSupportTicketTagsRequest $request,
        SupportTicket                  $ticket,
        UpdateSupportTicketTagsAction  $action
    ): JsonResponse
    {
        $ticket = $action->execute($ticket, $request->validated()['tags'] ?? []);

        return self::successfulResponseWithData(new AdminSupportTicketResource($ticket));
    }

    /**
     * @OA\Get(
     *     path="/api/admin/support/users/{userId}/tickets",
     *     summary="List tickets for user",
     *     tags={"Admin Support"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(name="userId", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/SupportTicket"))
     *         )
     *     )
     * )
     */
    public function userTickets(int $userId, ListSupportTicketsAction $action): JsonResponse
    {
        $tickets = $action->execute(['user_id' => $userId]);

        return self::successfulResponseWithData(AdminSupportTicketResource::collection($tickets));
    }

    /**
     * @OA\Post(
     *     path="/api/admin/support/tickets/{id}/transfer-to-ai",
     *     summary="Transfer ticket to AI",
     *     tags={"Admin Support"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/SupportTicket")
     *     )
     * )
     */
    public function transferToAi(SupportTicket $ticket, TransferToAiAction $action): JsonResponse
    {
        $ticket = $action->execute($ticket);

        return self::successfulResponseWithData(new AdminSupportTicketResource($ticket->load(['messages.user', 'user'])));
    }

    /**
     * @OA\Get(
     *     path="/api/admin/support/tickets/{id}/messages",
     *     summary="List messages for a ticket (pagination)",
     *     tags={"Admin Support"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="before_id", in="query", @OA\Schema(type="integer"), description="Load messages older than this ID"),
     *     @OA\Parameter(name="limit", in="query", @OA\Schema(type="integer"), description="Number of messages to load"),
     *     @OA\Response(response=200, description="Successful operation")
     * )
     */
    public function messages(Request $request, SupportTicket $ticket, ListSupportTicketMessagesAction $action): JsonResponse
    {
        $messages = $action->execute(
            $ticket,
            $request->input('before_id'),
            (int) $request->input('limit', 5)
        );

        return self::successfulResponseWithData(AdminSupportMessageResource::collection($messages));
    }

    /**
     * @OA\Post(
     *     path="/api/admin/support/tickets/{id}/read-messages",
     *     summary="Mark specific messages as read",
     *     tags={"Admin Support"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(@OA\Property(property="ids", type="array", @OA\Items(type="integer")))
     *     ),
     *     @OA\Response(response=200, description="Updated count")
     * )
     */
    public function markMessagesAsRead(Request $request, SupportTicket $ticket, MarkSupportMessagesReadAction $action): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer'
        ]);

        $count = $action->execute($ticket, $request->input('ids'));

        return response()->json(['updated' => $count]);
    }
}
