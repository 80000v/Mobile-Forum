<?php
/*
 * 调试类函数
 * 万林赞 2014.4.16
 *
 */

/**
 * @param $var
 * 打印变量
 */
function p($var)
{
    echo '<pre style="background: #eee;padding:10px;margin:10px;">';
    if (is_null($var) || is_bool($var) || empty($var)) {
        var_dump($var);
    } else {
        print_r($var);
    }
    echo '</pre>';
}

/**
 * 打印所有自定义变量
 */
function p_const()
{
    $const = get_defined_constants(true);
    p($const['user']);
}

/**
 * @param $str
 * @param string $dir
 * 调试打印信息到文件中
 */
//function p_log($str, $dir = path_root)
//{
//    $str = print_r($str,true);
//    $filename = $dir . date("Ymd") . '.txt';
//    $fp = fopen($filename, 'a+');
//    fwrite($fp, $str . "\r\n");
//    fclose($fp);
//}