<?php

namespace App\Api\V1\Resources\Support;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'SupportTicketResource',
    title: 'Support Ticket Resource',
)]
class SupportTicketResource extends JsonResource
{
    #[OA\Property(property: 'id', type: 'integer')]
    #[OA\Property(property: 'subject', type: 'string')]
    #[OA\Property(property: 'status', type: 'string', enum: ['new', 'accepted', 'done', 'archived', 'deleted'])]
    #[OA\Property(property: 'handledBy', type: 'string', enum: ['human', 'ai'], nullable: true)]
    #[OA\Property(property: 'unreadCount', type: 'integer', nullable: true, description: 'Only present when the ticket list is loaded with an unread count')]
    #[OA\Property(property: 'readAt', type: 'string', format: 'date-time', nullable: true)]
    #[OA\Property(property: 'lastMessage', ref: '#/components/schemas/SupportMessageResource', nullable: true)]
    #[OA\Property(property: 'messages', type: 'array', items: new OA\Items(ref: '#/components/schemas/SupportMessageResource'))]
    #[OA\Property(property: 'publicMessages', type: 'array', items: new OA\Items(ref: '#/components/schemas/SupportMessageResource'))]
    #[OA\Property(property: 'product', type: 'object', nullable: true, description: 'The product this ticket was opened about, if the customer started it from a product page - same raw shape as /catalog/products/{slug}')]
    #[OA\Property(property: 'createdAt', type: 'string', format: 'date-time')]
    #[OA\Property(property: 'updatedAt', type: 'string', format: 'date-time')]
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'subject' => $this->subject,
            'status' => $this->status,
            'handledBy' => $this->handled_by,
            'unreadCount' => $this->when(array_key_exists('unreadCount', $this->resource->getAttributes()), fn () => (int) $this->unreadCount),
            'readAt' => $this->read_at ? $this->read_at->toIso8601String() : null,
            'lastMessage' => new SupportMessageResource($this->whenLoaded('lastMessage')),
            'messages' => SupportMessageResource::collection($this->whenLoaded('messages')),
            'publicMessages' => SupportMessageResource::collection($this->whenLoaded('publicMessages')),
            'product' => $this->whenLoaded('product'),
            'user' => $this->whenLoaded('user'),
            'createdAt' => $this->created_at->toIso8601String(),
            'updatedAt' => $this->updated_at->toIso8601String(),
        ];
    }
}
