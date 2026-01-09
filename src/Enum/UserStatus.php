<?php

namespace App\Enum;

enum UserStatus: string
{
    case ACTIVE = 'active';
    case DISABLED = 'disabled';
    case ARCHIVED = 'archived';

    public function getLabel(): string
    {
        return match($this) {
            self::ACTIVE => 'Active',
            self::DISABLED => 'Disabled',
            self::ARCHIVED => 'Archived',
        };
    }

    public function getColorClass(): string
    {
        return match($this) {
            self::ACTIVE => 'status-active',
            self::DISABLED => 'status-disabled',
            self::ARCHIVED => 'status-archived',
        };
    }
}