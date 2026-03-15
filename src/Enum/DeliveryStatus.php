<?php

namespace App\Enum;

enum DeliveryStatus: string
{
    case PENDING = 'pending';
    case IN_TRANSIT = 'in_transit';
    case DELIVERED = 'delivered';
    case RETURNED = 'returned';

    public function getLabel(): string
    {
        return match($this) {
            self::PENDING => 'Pending',
            self::IN_TRANSIT => 'In Transit',
            self::DELIVERED => 'Delivered',
            self::RETURNED => 'Returned',
        };
    }

    public function getColorClass(): string
    {
        return match($this) {
            self::PENDING => 'status-pending',
            self::IN_TRANSIT => 'status-in-transit',
            self::DELIVERED => 'status-delivered',
            self::RETURNED => 'status-returned',
        };
    }
}