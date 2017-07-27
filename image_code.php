<?php
/**
 * Created by PhpStorm.
 * User: yuyi
 * Date: 17/7/27
 * Time: 23:09
 */

// 建立一幅 100X30 的图像
$im = imagecreate(100, 35);

// 白色背景和蓝色文本
imagecolorallocate($im, 255, 255, 255);
$textcolor = imagecolorallocate($im, 0, 0, 255);

// 把字符串写在图像左上角
imagestring($im, 5, 0, 0, "Hello world!", $textcolor);

// 输出图像
//header("Content-type: image/png");
imagepng($im, __DIR__."/text_code.png");