<?php
/**
 * Created by PhpStorm.
 * User: yuyi
 * Date: 17/7/27
 * Time: 23:09
 */

$font  = __DIR__."/msyh.ttf";
$size  = 30; //文字大小
$angle = rand(-5, 5); //旋转大小
$text  = rand(100000, 999999);

//得到文字的大小 这样才能准确判定文字水印可以写入的坐标范围
$temp = imagettfbbox($size, $angle,
    $font, $text
);//取得使用 TrueType 字体的文本的范围
$w = $temp[2] - $temp[6];
$h = $temp[3] - $temp[7];


// 建立一幅 100X30 的图像
$im = imagecreate($w+20, $h+20);

// 白色背景和蓝色文本
imagecolorallocate($im, 255, 255, 255);
$textcolor = imagecolorallocate($im, 0, 0, 255);

// 把字符串写在图像左上角
//imagestring($im, 5, 0, 0, "Hello world!", $textcolor);

for ($i = 0; $i <10000; $i++) {
    imagefttext(
        $im,
        200,
        0,
        rand(0, $w),     //使文字有10 边缘
        rand(0,$h+20),  //使文字有10 边缘
        imagecolorallocate($im, rand(0,255), rand(0,255), rand(0,255)),
        $font,
        "。"
    );
}

imagefttext(
    $im,
    $size,
    $angle,
    10,     //使文字有10 边缘
    $h+10,  //使文字有10 边缘
    $textcolor,
    $font,
    $text
);

// 输出图像
header("Content-type: image/png");
imagepng($im);//, __DIR__."/text_code.png"); //保存图片的时候使用第二个参数

//真正作为验证码的时候使用如下方式使用
//<img src="http://php.talk.com/image_code.php" />