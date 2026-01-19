<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    title: "InsiderOne SMS Bulk Messaging API",
    description: "High-performance bulk SMS system with Redis cache, queue management and idempotency. Handles 200K+ messages with rate limiting (50 req/min), automatic retry logic, and comprehensive monitoring."
)]
#[OA\Server(url: "/api/v1", description: "API V1 Endpoint")]
abstract class Controller
{
    //
}
