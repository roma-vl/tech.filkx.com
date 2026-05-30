<?php

namespace App\Api\V1\Exceptions;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AttributeNotFoundException extends NotFoundHttpException
{
    public function __construct(string $message = 'Attribute not found.', ?\Throwable $previous = null, int $code = 0, array $headers = [])
    {
        parent::__construct($message, $previous, $code, $headers);
    }
}
