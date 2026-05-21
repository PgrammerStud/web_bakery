<?php

namespace App\Exception;

class DuplicateOrderException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct('A duplicate order was detected. Please wait before placing another order.');
    }
}