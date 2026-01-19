<?php

namespace App\Enums;

enum MessageSendStatus: string
{

    case PENDING = 'pending';
    case SENT = 'sent';
    case FAILED = 'failed';
}
