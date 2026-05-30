<?php

namespace App\Api\V1\Actions\Cart;

use App\Api\V1\Dto\MergeCartDto;
use App\Api\V1\Repositories\CartRepositoryInterface;
use Symfony\Component\HttpFoundation\Response;

class MergeCartsAction
{
    public function __construct(
        protected CartRepositoryInterface $cartRepository
    ) {}

    public function execute(MergeCartDto $dto): void
    {
        $user = auth('api')->user();
        if (! $user) {
            throw new \RuntimeException('Unauthorized', Response::HTTP_UNAUTHORIZED);
        }

        $userCart = $this->cartRepository->findOrCreateForUser($user->id);
        $anonCart = $this->cartRepository->findBySessionId($dto->sessionId);

        if ($anonCart && $anonCart->id !== $userCart->id) {
            $this->cartRepository->mergeCarts($userCart, $anonCart);
        }
    }
}
