<?php

namespace App\Exception;

class InvalidQuantityException extends \RuntimeException
{
    public function __construct(int $quantity)
    {
        parent::__construct(
            "Invalid quantity '{$quantity}'. Quantity must be between 1 and 999."
        );
    }
}