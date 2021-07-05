<?php

namespace App\Helpers;

class ArrayHelper
{
    public static function array_keys_exists(array $keys, array $arr) {
        return !array_diff_key(array_flip($keys), $arr);
    }

    public static function test()
    {
        return 'test';
    }
}
