<?php
/**
 * Created by PhpStorm.
 * User: AmyLiu
 * Date: 2019/10/25
 * Time: 15:10
 */
function jointUrl($dirList, $last) {
    $url = '';
    foreach($dirList as $key=>$val) {
        if($key < $last) {
            $url .= $val.DIRECTORY_SEPARATOR;
        }else if($key == $last) {
            $url .= $val;
        }
    }
    return $url;
}