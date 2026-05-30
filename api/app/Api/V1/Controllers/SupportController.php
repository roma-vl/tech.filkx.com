<?php

namespace App\Api\V1\Controllers;

use App\Models\SupportTicket;
use App\Models\SupportMessage;
use App\Api\V1\Enum\SupportStatusEnum;
use App\Api\V1\Resources\Support\SupportMessageResource;
use App\Api\V1\Resources\Support\SupportTicketResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SupportController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $tickets = SupportTicket::where('user_id', $user->id)
            ->with(['lastMessage'])
            ->withCount(['unreadMessagesForUser as unreadCount'])
            ->latest('updated_at')
            ->get();

        return self::successfulResponseWithData(SupportTicketResource::collection($tickets));
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'file' => 'nullable|file|max:10240',
        ]);

        $user = $request->user();

        $ticket = SupportTicket::create([
            'user_id' => $user->id,
            'subject' => $request->input('subject'),
            'status' => 'new',
            'handled_by' => 'human',
        ]);

        $filePath = null;
        $fileType = null;
        $fileName = null;
        $fileSize = null;

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filePath = $file->store('support_files', 'public');
            $fileType = $file->getClientMimeType();
            $fileName = $file->getClientOriginalName();
            $fileSize = $file->getSize();
        }

        $ticket->messages()->create([
            'user_id' => $user->id,
            'message' => $request->input('message'),
            'file_path' => $filePath,
            'file_type' => $fileType,
            'file_name' => $fileName,
            'file_size' => $fileSize,
            'is_admin' => false,
        ]);

        return self::successfulResponseWithData(
            new SupportTicketResource($ticket->load('messages')),
        );
    }

    public function show(SupportTicket $ticket): JsonResponse
    {
        if ($ticket->user_id !== auth('api')->id()) {
            return self::errorResponse('Access denied', Response::HTTP_FORBIDDEN);
        }

        return self::successfulResponseWithData(
            new SupportTicketResource($ticket->load(['publicMessages.user', 'user']))
        );
    }

    public function markAsRead(SupportTicket $ticket): Response
    {
        if ($ticket->user_id !== auth('api')->id()) {
            abort(403);
        }

        $ticket->update(['read_at' => now()]);
        $ticket->messages()->where('is_admin', true)->whereNull('read_at')->update(['read_at' => now()]);

        return response()->noContent();
    }

    public function sendMessage(Request $request, SupportTicket $ticket): JsonResponse
    {
        if ($ticket->user_id !== auth('api')->id()) {
            return self::errorResponse('Access denied', Response::HTTP_FORBIDDEN);
        }

        $request->validate([
            'message' => 'nullable|string',
            'file' => 'nullable|file|max:10240',
        ]);

        if (!$request->input('message') && !$request->hasFile('file')) {
            return self::errorResponse('Message or file is required', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $filePath = null;
        $fileType = null;
        $fileName = null;
        $fileSize = null;

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filePath = $file->store('support_files', 'public');
            $fileType = $file->getClientMimeType();
            $fileName = $file->getClientOriginalName();
            $fileSize = $file->getSize();
        }

        $message = $ticket->messages()->create([
            'user_id' => auth('api')->id(),
            'message' => $request->input('message'),
            'file_path' => $filePath,
            'file_type' => $fileType,
            'file_name' => $fileName,
            'file_size' => $fileSize,
            'is_admin' => false,
        ]);

        if ($ticket->status === SupportStatusEnum::DONE) {
            $ticket->update(['status' => SupportStatusEnum::ACCEPTED]);
        }

        return self::successfulResponseWithData(new SupportMessageResource($message));
    }

    public function transfer(SupportTicket $ticket): JsonResponse
    {
        if ($ticket->user_id !== auth('api')->id()) {
            return self::errorResponse('Access denied', Response::HTTP_FORBIDDEN);
        }
        $ticket->update(['handled_by' => 'human']);
        return self::successfulResponseWithData(new SupportTicketResource($ticket->load('messages')));
    }

    public function transferToAi(SupportTicket $ticket): JsonResponse
    {
        if ($ticket->user_id !== auth('api')->id()) {
            return self::errorResponse('Access denied', Response::HTTP_FORBIDDEN);
        }
        $ticket->update(['handled_by' => 'ai']);
        return self::successfulResponseWithData(new SupportTicketResource($ticket->load('messages')));
    }
}
