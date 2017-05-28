<?php

$u_array_walk = function (array $arr, callable $cb) {
    foreach ($arr as $key => $value) {
        $cbRet = $cb($value, $key);

        if ($cbRet) {
            return $cbRet;
        }
    }
};
/*
function u_array_unique(array $arr) {
    $res2 = array();
    foreach ($arr as $key => $val) {
        $res2[$val] = true;
    }
    
    return array_keys($res2);
}
*/