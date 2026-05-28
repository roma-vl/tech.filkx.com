<?php

namespace App\Api\Admin\Controllers;

use App\Api\Admin\Actions\SupportSnippet\CreateSupportSnippetAction;
use App\Api\Admin\Actions\SupportSnippet\DeleteSupportSnippetAction;
use App\Api\Admin\Actions\SupportSnippet\ListSupportSnippetsAction;
use App\Api\Admin\Actions\SupportSnippet\UpdateSupportSnippetAction;
use App\Api\Admin\Requests\SupportSnippet\CreateSupportSnippetRequest;
use App\Api\Admin\Requests\SupportSnippet\UpdateSupportSnippetRequest;
use App\Models\SupportSnippet;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class AdminSupportSnippetController extends BaseApiController
{
    /**
     * @OA\Get(
     *     path="/api/admin/support/snippets",
     *     summary="List all support snippets",
     *     tags={"Admin Support Snippets"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="List of snippets",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="title", type="string", example="Welcome Message"),
     *                 @OA\Property(property="content", type="string", example="Hello, how can I help you?"),
     *                 @OA\Property(property="created_at", type="string", example="2026-02-07 11:30:00")
     *             ))
     *         )
     *     )
     * )
     */
    public function index(ListSupportSnippetsAction $action): JsonResponse
    {
        $snippets = $action->execute();

        return self::successfulResponseWithData($snippets);
    }

    /**
     * @OA\Post(
     *     path="/api/admin/support/snippets",
     *     summary="Create a new support snippet",
     *     tags={"Admin Support Snippets"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="title", type="string", example="Greeting"),
     *             @OA\Property(property="content", type="string", example="Hi there!")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Snippet created",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function store(CreateSupportSnippetRequest $request, CreateSupportSnippetAction $action): JsonResponse
    {
        $snippet = $action->execute($request->validated());

        return self::successfulResponseWithData($snippet, Response::HTTP_CREATED);
    }

    /**
     * @OA\Put(
     *     path="/api/admin/support/snippets/{id}",
     *     summary="Update an existing support snippet",
     *     tags={"Admin Support Snippets"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="title", type="string", example="Updated Title"),
     *             @OA\Property(property="content", type="string", example="Updated content...")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Snippet updated",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function update(UpdateSupportSnippetRequest $request, SupportSnippet $snippet, UpdateSupportSnippetAction $action): JsonResponse
    {
        $updated = $action->execute($snippet, $request->validated());

        return self::successfulResponseWithData($updated);
    }

    /**
     * @OA\Delete(
     *     path="/api/admin/support/snippets/{id}",
     *     summary="Delete a support snippet",
     *     tags={"Admin Support Snippets"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\Response(response=204, description="No content")
     * )
     */
    public function destroy(SupportSnippet $snippet, DeleteSupportSnippetAction $action): JsonResponse
    {
        $action->execute($snippet);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
