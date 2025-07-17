<?php

namespace App\Enums;

use Illuminate\Support\Str;


enum BookingStatus: string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case CANCELLED = 'cancelled';

    /**
     * Get the values of the enum.
     *
     * @return array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }


    public static function commaSeparatedValues(): string
    {
        return implode(', ', array_column(self::cases(), 'value'));
    }
} 