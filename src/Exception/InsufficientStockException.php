<?php

namespace App\Exception;

class InsufficientStockException extends \RuntimeException
{
    public function __construct(string $productName, int $available, int $requested)
    {
        parent::__construct(
            "'{$productName}' only has {$available} in stock, but {$requested} was requested."
        );
    }
}