<?php

namespace App\Traits;

trait EnumsWithOptions
{
    public static function toOptions(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[] = [
                'label' => $case->value,
                'value' => $case->value,
            ];
        }

        return $options;
    }

    /**
     * Get all scalar equivalent values of Backed Enum as an array
     * Ex: [1, 2, 3] OR ['Pending', 'Accepted', 'Declined']
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get all scalar equivalent values and name of Backed Enum as an array
     * Ex: [1 => 'Pending', 2 => 'Accepted']
     */
    public static function valueNamePairs(): array
    {
        return array_combine(
            array_column(self::cases(), 'value'),
            array_column(self::cases(), 'name')
        );
    }

    /**
     * Get all scalar equivalent names and values of Backed Enum as an array
     * Ex: ['Pending' => 1, 'Accepted' => 2]
     */
    public static function nameValuePairs(): array
    {
        return array_combine(
            array_column(self::cases(), 'name'),
            array_column(self::cases(), 'value')
        );
    }
}
