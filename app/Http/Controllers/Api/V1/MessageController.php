<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\MessageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class MessageController extends Controller
{
    public function __construct(
        private MessageService $messageService
    ) {}

    #[OA\Get(
        path: "/messages/sent",
        summary: "Get sent messages",
        tags: ["Messages"],
        parameters: [
            new OA\Parameter(
                name: "page",
                in: "query",
                required: false,
                description: "Page number",
                schema: new OA\Schema(type: "integer", default: 1, example: 1)
            ),
            new OA\Parameter(
                name: "per_page",
                in: "query",
                required: false,
                description: "Items per page",
                schema: new OA\Schema(type: "integer", default: 50, example: 50)
            )
        ],
        responses: [
            new OA\Response(
                response: "200",
                description: "Success",
                content: new OA\JsonContent(ref: "#/components/schemas/SentMessagesResponse")
            )
        ]
    )]
    public function getSentMessages(Request $request): JsonResponse
    {
        $perPage = (int) $request->get('per_page', 50);

        $messages = $this->messageService->getSentMessages($perPage);

        return response()->json([
            'success' => true,
            'data' => $messages->items(),
            'meta' => [
                'current_page' => $messages->currentPage(),
                'per_page' => $messages->perPage(),
                'total' => $messages->total(),
                'last_page' => $messages->lastPage(),
            ]
        ]);
    }
}
