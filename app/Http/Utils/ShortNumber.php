<?php

namespace App\Http\Utils;

class ShortNumber {

    public static function number_shorten($number, $precision = 1) {
        if ($number < 1000) {
            return $number;
        }

        $units = ['k', 'M', 'B', 'T'];
        $power = floor(log($number, 1000));

        return round($number / pow(1000, $power), $precision) . $units[$power - 1];
    }
}
