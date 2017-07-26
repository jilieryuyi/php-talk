<?php
/**
 * Created by PhpStorm.
 * User: yuyi
 * Date: 17/7/27
 * Time: 07:42
 */
$image      = __DIR__."/1.jpeg";
//判断是否为图片
$image_size = getimagesize($image);
if (!$image_size) {
    die("不是图片");
}

//得到文件扩展
$ext = pathinfo($image, PATHINFO_EXTENSION);
echo "图片扩展为：",$ext, "\r\n";

//得到真实的图片格式
$handle = fopen($image,"r");
$data   = fread($handle,10);
$arr    = unpack("C*", $data);

$str      = "";
$start    = false;
$is_start = false;

//遇到可识别的字母字符 开始读 直到遇到不可识别的字符结束读
foreach ($arr as $v) {
    if ($v >= 65 && $v <=122) {
        $start = true;
    } else {
        $start = false;
    }

    if ($start) {
        $str .=chr($v);
        $is_start = true;
    }

    if ($is_start && !$start) {
        break;
    }
}

fclose($handle);

echo "真是的图片类型为：",$str,"\r\n";
//JFIF即jpg和jpeg的真是格式