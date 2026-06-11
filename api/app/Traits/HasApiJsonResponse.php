<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;

trait HasApiJsonResponse
{
    public static function successfulResponse(mixed $message = null, mixed $data = null, ?int $status = null): JsonResponse
    {
        if (is_int($message)) {
            $status = $message;
            $message = null;
        }

        $status ??= Response::HTTP_OK;

        $response = ['status' => 'success'];

        if ($message !== null) {
            $response['message'] = $message;
        }

        if ($data !== null) {
            $response['data'] = self::convertToCamelCase($data);
        }

        return response()->json($response, $status, [], JSON_UNESCAPED_UNICODE);
    }

    public static function successfulResponseWithData(mixed $data, ?int $status = null, ?string $message = null): JsonResponse
    {
        $status ??= Response::HTTP_OK;

        if (
            is_array($data) ||
            $data instanceof Model ||
            $data instanceof LengthAwarePaginator ||
            $data instanceof Paginator ||
            $data instanceof Collection ||
            $data instanceof JsonResource
        ) {
            $data = self::convertToCamelCase($data);
        }

        $response = [
            'status' => 'success',
            'data' => $data,
        ];

        if ($message) {
            $response['message'] = $message;
        }

        return response()->json(
            $response,
            $status,
            [],
            JSON_UNESCAPED_UNICODE
        );
    }

    public static function redirect(string $route, ?int $status = null, array $headers = []): JsonResponse
    {
        $status ??= Response::HTTP_SEE_OTHER;

        return response()->json(
            [
                'status' => 'success',
                'data' => [
                    'action' => 'redirect',
                    'url' => $route,
                ],
            ],
            $status,
            $headers,
            JSON_UNESCAPED_UNICODE
        );
    }

    public static function errorResponse(string $message, ?int $status = null): JsonResponse
    {
        $status ??= Response::HTTP_INTERNAL_SERVER_ERROR;

        return response()->json(
            [
                'status' => 'error',
                'message' => $message,
            ],
            $status,
            [],
            JSON_UNESCAPED_UNICODE
        );
    }

    public static function errorResponseWithData(string $message, mixed $data, ?int $status = null): JsonResponse
    {
        $status ??= Response::HTTP_INTERNAL_SERVER_ERROR;

        return response()->json(
            [
                'status' => 'error',
                'message' => $message,
                'data' => $data,
            ],
            $status,
            [],
            JSON_UNESCAPED_UNICODE
        );
    }

    public static function validationErrorResponse(string $errorKey, array $errorMessages, ?string $commonMessage = null): JsonResponse
    {
        return response()->json(
            [
                'message' => $commonMessage ?? $errorMessages[0],
                'errors' => [
                    $errorKey => $errorMessages,
                ],
            ],
            Response::HTTP_UNPROCESSABLE_ENTITY,
            [],
            JSON_UNESCAPED_UNICODE
        );
    }

    public static function convertToCamelCase(mixed $data): array
    {
        if ($data instanceof JsonResource) {
            if ($data instanceof AnonymousResourceCollection && $data->resource instanceof AbstractPaginator) {
                $response = $data->response()->getData(true);
                $data = $response['meta'] ?? [];
                if (isset($response['links'])) {
                    foreach ($response['links'] as $linkKey => $linkValue) {
                        $data[$linkKey] = $linkValue;
                    }
                }
                $data['data'] = $response['data'];
            } else {
                $data = $data->resolve();
            }
        }

        if (! is_array($data)) {
            if (method_exists($data, 'toArray')) {
                $data = $data->toArray();
            } else {
                return (array) $data;
            }
        }

        $converted = [];

        foreach ($data as $key => $value) {
            $key = str_replace(' ', '', ucwords(str_replace('_', ' ', $key)));
            $key[0] = strtolower($key[0]);

            if (
                is_array($value) ||
                $value instanceof Model ||
                $value instanceof LengthAwarePaginator ||
                $value instanceof Paginator ||
                $value instanceof Collection ||
                $value instanceof JsonResource
            ) {
                $value = self::convertToCamelCase($value);
            }

            $converted[$key] = $value;
        }

        return $converted;
    }
}
