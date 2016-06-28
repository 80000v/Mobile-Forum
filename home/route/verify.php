<?php

$width = 80;
$height = 30; 
$background = array(255, 255, 255); 
$len = 4; 
$fontsize = 20;

//创建画布
$img = imagecreatetruecolor($width, $height);
$color = imagecolorallocate($img, $background[0], $background[1], $background[2]);
imagefill($img, 0, 0, $color);

//画线
$color = imagecolorallocate($img, 220, 220, 220);
for ($i = 1, $l = $height / 5; $i < $l; $i++) {
    $step = $i * 5;
    imageline($img, 0, $step, $width, $step, $color);
}
for ($i = 1, $l = $width / 10; $i < $l; $i++) {
    $step = $i * 10;
    imageline($img, $step, 0, $step, $height, $color);
}

//生成字符串
$code_str = '23456789abcdefghjkmnpqrstuvwsyz';
$code = '';
for ($i = 0; $i < $len; $i++) {
    $code .= $code_str[mt_rand(0, strlen($code_str) - 1)];
}
$_SESSION['code'] = strtoupper($code);

//字符串写入画布
$font = path_static . 'font/font.ttf';//字体文件物理位置
$x = ($width - 10) / $len;
for ($i = 0; $i < $len; $i++) {
    $color = imagecolorallocate($img, mt_rand(50, 155), mt_rand(50, 155), mt_rand(50, 155));
    imagettftext($img, $fontsize, mt_rand(-30, 30), $x * $i + mt_rand(6, 10), mt_rand($height / 1.3, $height - 5), $color, $font, $code[$i]);
}

//画点
for ($i = 0; $i < 50; $i++) {
    imagesetpixel($img, mt_rand(0, $width), mt_rand(0, $height), $color);
}

//画线
for ($i = 0; $i < 2; $i++) {
    imageline($img, mt_rand(0, $width), mt_rand(0, $height), mt_rand(0, $width), mt_rand(0, $height), $color);
}
//画圆弧
for ($i = 0; $i < 1; $i++) {

    imagearc($img, mt_rand(0, $width), mt_rand(0, $height), mt_rand(0, $width), mt_rand(0, $height)
        , mt_rand(0, 160), mt_rand(0, 200), $color);
}
imagesetthickness($img, 1);//// 设置画线宽度

header("Content-type:image/png");
imagepng($img);
imagedestroy($img);
