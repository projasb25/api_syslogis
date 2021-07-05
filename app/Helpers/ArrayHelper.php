<?php

namespace App\Helpers;

class ArrayHelper
{
    public static function array_keys_exists(array $keys, array $arr) {
        return !array_diff_key(array_flip($keys), $arr);
    }

    public static function search_by_two_keys($array, $key1, $key2, $val1, $val2, $res)
    {
        foreach ($array as $key => $value) {
            if ($value->$key1 === $val1 && $value->$key2 === $val2) {
                return $value->$res;
            }
        }
    }

    public static function sum_total_by_key($array, $key1, $val1, $key_sum)
    {
        $sum = 0;
        foreach ($array as $key => $value) {
            if ($value->$key1 === $val1) {
                $sum += $value->$key_sum;
            }
        }
        return $sum;
    }
}
