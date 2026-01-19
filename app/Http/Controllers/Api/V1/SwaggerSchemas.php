<?php

namespace App\Http\Controllers\Api\V1;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "MessageSend",
    type: "object",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "message_id", type: "integer", example: 5),
        new OA\Property(property: "customer_id", type: "integer", example: 42),
        new OA\Property(property: "phone_number", type: "string", example: "905551234567"),
        new OA\Property(property: "message_content", type: "string", example: "Hello! Your order has been shipped."),
        new OA\Property(property: "status", type: "string", enum: ["pending", "sent", "failed"], example: "sent"),
        new OA\Property(property: "webhook_message_id", type: "string", nullable: true, example: "msg_65a0b12f2c9d4"),
        new OA\Property(property: "sent_at", type: "string", format: "date-time", example: "2026-01-19T14:30:00.000000Z"),
        new OA\Property(property: "created_at", type: "string", format: "date-time", example: "2026-01-19T14:25:00.000000Z"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time", example: "2026-01-19T14:30:00.000000Z")
    ]
)]
#[OA\Schema(
    schema: "PaginationMeta",
    type: "object",
    properties: [
        new OA\Property(property: "current_page", type: "integer", example: 1),
        new OA\Property(property: "per_page", type: "integer", example: 50),
        new OA\Property(property: "total", type: "integer", example: 250),
        new OA\Property(property: "last_page", type: "integer", example: 5)
    ]
)]
#[OA\Schema(
    schema: "SentMessagesResponse",
    type: "object",
    properties: [
        new OA\Property(property: "success", type: "boolean", example: true),
        new OA\Property(
            property: "data",
            type: "array",
            items: new OA\Items(ref: "#/components/schemas/MessageSend")
        ),
        new OA\Property(property: "meta", ref: "#/components/schemas/PaginationMeta")
    ]
)]
class SwaggerSchemas {}
