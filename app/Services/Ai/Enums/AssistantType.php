<?php

namespace App\Services\Ai\Enums;

enum AssistantType: string
{
    case SUPPORT = 'support';
    case DEV = 'dev';
    case SALES = 'sales';

    public function promptFilename(): string
    {
        return match ($this) {
            self::SUPPORT => 'biscuits_support.md',
            self::DEV => 'biscuits_dev.md',
            self::SALES => 'biscuits_sales.md',
        };
    }
}
