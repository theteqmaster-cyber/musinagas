<?php

namespace App\Services;

class OrderRefService
{
    /**
     * Generate a unique order reference code like MG-00124
     */
    public static function generate(): string
    {
        $number = rand(10000, 99999);
        return 'MG-' . $number;
    }
}
