<?php

namespace App\Api\V1\Exceptions;

use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

class DeliveryProviderUnavailableException extends ServiceUnavailableHttpException
{
    public function __construct(string $message = 'Інтеграція з Новою Поштою тимчасово недоступна.', ?\Throwable $previous = null, int $code = 0, array $headers = [])
    {
        parent::__construct(null, $message, $previous, $code, $headers);
    }
}
