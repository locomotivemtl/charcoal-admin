<?php

declare(strict_types=1);

namespace Charcoal\Admin\Support;

/**
 * String Formatter
 */
class Formatter
{
    /**
     * Formats bytes into a human-readable size.
     *
     * @param  float|int $bytes The number of bytes to format.
     * @return string
     */
    public static function formatBytes($bytes): string
    {
        if ($bytes === 0) {
            return '0';
        }

        $units = [ 'B', 'KB', 'MB', 'GB', 'TB' ];
        $base  = log($bytes, 1024);
        $floor = floor($base);
        $unit  = $units[$floor];
        $size  = round(pow(1024, ($base - $floor)), 2);

        $locale = localeconv();
        $size   = number_format(
            $size,
            2,
            $locale['decimal_point'],
            $locale['thousands_sep']
        );

        return rtrim($size, '.0').' '.$unit;
    }
}
