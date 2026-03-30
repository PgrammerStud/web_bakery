<?php

namespace App\Enum;

enum PaymentStatus: string
{
    case GCASH = 'gcash';
    case MAYA = 'maya';
    case CASH = 'cash';

    public function getLabel(): string
    {
        return match($this) {
            self::GCASH => 'GCash',
            self::MAYA => 'Maya',
            self::CASH => 'Cash',
        };
    }

    public function getColorClass(): string
    {
        return match($this) {
            self::GCASH => 'status-gcash',
            self::MAYA => 'status-maya',
            self::CASH => 'status-cash',
        };
    }
}