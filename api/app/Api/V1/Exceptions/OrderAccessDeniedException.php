<?php

namespace App\Api\V1\Exceptions;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class OrderAccessDeniedException extends AccessDeniedHttpException
{
    public function __construct(string $message = 'Access denied.', ?\Throwable $previous = null, int $code = 0, array $headers = [])
    {
        parent::__construct($message, $previous, $code, $headers);
    }
}
