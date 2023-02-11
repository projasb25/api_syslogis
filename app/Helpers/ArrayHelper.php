<?php

namespace App\Helpers;

class ArrayHelper
{
    public static function array_keys_exists(array $keys, array $arr)
    {
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

    public static function array_to_xml( $data, &$xml_data ) {
        foreach( $data as $key => $value ) {
            if( is_array($value) ) {
                if( is_numeric($key) ){
                    $key = 'item'.$key; //dealing with <0/>..<n/> issues
                }
                $subnode = $xml_data->addChild($key);
                ArrayHelper::array_to_xml($value, $subnode);
            } else {
                $xml_data->addChild("$key",htmlspecialchars("$value"));
            }
         }
    }
}
