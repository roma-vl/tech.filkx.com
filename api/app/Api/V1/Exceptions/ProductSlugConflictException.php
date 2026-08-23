<?php

namespace App\Api\V1\Exceptions;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class ProductSlugConflictException extends UnprocessableEntityHttpException
{
    public function __construct(string $message, ?\Throwable $previous = null, int $code = 0, array $headers = [])
    {
        parent::__construct($message, $previous, $code, $headers);
    }
}
