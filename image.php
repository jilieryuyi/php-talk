<?php
/**
 * Created by PhpStorm.
 * User: yuyi
 * Date: 17/7/24
 * Time: 22:04
 */

/*
header('Content-Type:application/x-download');
header("Content-type: image/gif");
header('Content-Disposition: attachment;filename='.$data[0]['sname'].'.gif');
*/
namespace Wing\Php;

/**
 * 图片水印处理
 */
class Image
{
    private $image_filename = "";
    private $image_size     = null;
    public function __construct($image_filename)
    {
        $this->image_filename = $image_filename;
        if (!file_exists($this->image_filename)) {
            die($this->image_filename."不存在");
        }
        //最简单的判别文件是否为图片
        $this->image_size = getimagesize($this->image_filename);
        if (!$this->image_size) {
            die($this->image_filename."非法图片");
        }
    }

    public function getType()
    {
        $imageTypeArray = [
            0=>'UNKNOWN',
            1=>'GIF',
            2=>'JPEG',
            3=>'PNG',
            4=>'SWF',
            5=>'PSD',
            6=>'BMP',
            7=>'TIFF_II',
            8=>'TIFF_MM',
            9=>'JPC',
            10=>'JP2',
            11=>'JPX',
            12=>'JB2',
            13=>'SWC',
            14=>'IFF',
            15=>'WBMP',
            16=>'XBM',
            17=>'ICO',
            18=>'COUNT'
        ];

        return strtolower($imageTypeArray[$this->image_size[2]]);
    }

    protected function getSource()
    {
        switch($this->image_size[2]){
            case 1: return imagecreatefromgif($this->image_filename);break;
            case 2: return imagecreatefromjpeg($this->image_filename);break;
            case 3: return imagecreatefrompng($this->image_filename);break;
            default:
                return null;
        }
    }

    /**
     * 设置图片水印
     */
    public function setImageMark()
    {

    }

    public function getWidth()
    {
        return $this->image_size[0];
    }

    public function getHeight()
    {
        return $this->image_size[1];
    }

    /**
     * 格式转换
     *
     * @param string $save_path
     * @param bool $force_save
     */
    public function toPng($save_path = "", $force_save = false)
    {
        if (!$save_path) {
            $path_info = pathinfo($this->image_filename);
            $save_path = $path_info["dirname"]."/".$path_info["filename"].".mark.png";
        }
        if (!$force_save && file_exists($save_path)) {
            echo "警告：文件已存在，如果需要覆盖写入请使用第二个参数，将其设置为true";
            return;
        }
        $im = $this->getSource();
        imagepng($im, $save_path);
    }

    //$font_name 比如 微软雅黑
    //按需可以添加更多的字体库支持
    protected static function getFont($font_name)
    {
        $fonts = [
            "微软雅黑" => __DIR__."/msyh.ttf"
        ];

        if (!isset($fonts[$font_name])) {
            return __DIR__."/msyh.ttf";
        }

        return $fonts[$font_name];
    }

    /**
     * 设置文字水印
     *
     * @param string $water_text
     * @param array $params
     * @return string
     */
    public function setTextMark(
        $water_text,
        $params = [
            "font"       => "微软雅黑", //字体
            "pos_x"      => 0,         //x轴位置
            "pos_y"      => 0,         //y轴位置
            "angle"      => 0,         //旋转角度
            "size"       => 30,        //文字大小
            "text_color" => "#000000", //文字颜色
            "safe_mode"  => true,      //安全模式，不破坏原有图片
            "save_path"  => ""
        ])
    {
        $default_params = [
            "font"       => "微软雅黑",         //字体
            "pos_x"      => 0,         //x轴位置
            "pos_y"      => 0,         //y轴位置
            "size"       => 30,
            "angle"      => 0,
            "text_color" => "#000000", //文字颜色
            "safe_mode"  => true,       //安全模式，不破坏原有图片
            "save_path"  => ""
        ];
        $params = array_merge($default_params, $params);
        $font_file = self::getFont($params["font"]);


        //文字水印
        $temp = imagettfbbox($params["size"],$params["angle"],
            $font_file, $water_text
        );//取得使用 TrueType 字体的文本的范围
        $w = $temp[2] - $temp[6];
        $h = $temp[3] - $temp[7];

        if ($params["pos_y"] <= 0) {
            $params["pos_y"] = $h;
        }
        //echo $w,"----",$h,"\r\n";
        unset($temp);

        $image_width = $this->getWidth();
        $image_height = $this->getHeight();

        $max_pos_x = $image_width - $w;
        if ($max_pos_x < 0) {
            $max_pos_x = 0;
        }

        $max_pos_y = $image_height;// - $h;
        if ($max_pos_y < 0) {
            $max_pos_y = 0;
        }

        if ($params["pos_x"] > $max_pos_x) {
            $params["pos_x"] = $max_pos_x;
        }

        if ($params["pos_y"] > $max_pos_y) {
            $params["pos_y"] = $max_pos_y;
        }

        $ground_image_source = $this->getSource();
        $R = hexdec(substr($params["text_color"],1,2));
        $G = hexdec(substr($params["text_color"],3,2));
        $B = hexdec(substr($params["text_color"],5));
        //imagestring($ground_image_source, $params["text_font"],
//        $params["pos_x"], $params["pos_y"], $water_text,
//            imagecolorallocate($ground_image_source, $R, $G, $B)
//        );

        //var_dump($params);

        imagefttext(
                $ground_image_source,
                $params["size"],
                $params["angle"],
                $params["pos_x"],
                $params["pos_y"],
                imagecolorallocate($ground_image_source, $R, $G, $B),
                $font_file,
                $water_text
        );


        $save_path = $params["save_path"];

        //自动生成保存路径
        if (!$save_path) {
            $path_info = pathinfo($this->image_filename);
            $save_path = $path_info["dirname"] . "/" . $path_info["filename"] . "-" . time() . rand(100000, 999999) . ".mark.png";
        }
        if ($params["safe_mode"] && file_exists($save_path)) {
            echo "警告：文件已存在".$save_path;
            return null;
        }

        //header("Content-type: image/png");
        //覆盖写入
        imagepng($ground_image_source, $save_path);
        unset($ground_image_source);
        return $save_path;
    }

    public function __destruct()
    {
        // TODO: Implement __destruct() method.
        unset($this->image_size);
    }
}


$img = new \Wing\Php\Image(__DIR__."/1.jpeg");
$img->setTextMark("hello wing",
["text_color" => "#ffffff", "size" => 60]
);
$img->toPng("", true);
exit;
// 建立一幅 100X30 的图像
$im = imagecreate(100, 35);

// 白色背景和蓝色文本
$bg = imagecolorallocate($im, 255, 255, 255);
$textcolor = imagecolorallocate($im, 0, 0, 255);

// 把字符串写在图像左上角
imagestring($im, 5, 0, 0, "Hello world!", $textcolor);

// 输出图像
header("Content-type: image/png");
imagepng($im);


function imageWaterMark($groundImage,$waterImage='',$newFileNAme='',$delete=false,$waterPos=5,$waterText='',$textFont=5,$textColor='#FF0000'){
    $isWaterImage = FALSE;
//读取水印文件
    if(!empty($waterImage)&&file_exists($waterImage)){
        $isWaterImage = TRUE;
        $water_info = getimagesize($waterImage);
        $water_w = $water_info[0];//取得水印图片的宽
        $water_h = $water_info[1];//取得水印图片的高

        switch($water_info[2]){
//取得水印图片的格式
            case 1:$water_im = imagecreatefromgif($waterImage);break;
            case 2:$water_im = imagecreatefromjpeg($waterImage);break;
            case 3:$water_im = imagecreatefrompng($waterImage);break;
            default:die("暂时不支持该水印的格式");break;
        }
    }

//读取背景图片
    if(!empty($groundImage)&&file_exists($groundImage)){

        $ground_info = getimagesize($groundImage);
        $ground_w = $ground_info[0];//取得背景图片的宽
        $ground_h = $ground_info[1];//取得背景图片的高

        switch($ground_info[2]){
//取得背景图片的格式
            case 1:$ground_im = imagecreatefromgif($groundImage);break;
            case 2:$ground_im = imagecreatefromjpeg($groundImage);break;
            case 3:$ground_im = imagecreatefrompng($groundImage);break;
            default:die("暂时不支持该图片的格式");break;
        }
    }
    else{
        die("图片不存在");
    }

    //水印位置
    if($isWaterImage){
        //图片水印
        $w = $water_w;
        $h = $water_h;
        $label = "图片的";
    }
    else{
        //文字水印
        $temp = imagettfbbox(ceil($textFont*5),0,"./cour.ttf",$waterText);//取得使用 TrueType 字体的文本的范围
        $w = $temp[2] - $temp[6];
        $h = $temp[3] - $temp[7];
        unset($temp);
        $label = "文字区域";
    }

    if( ($ground_w<$w) || ($ground_h<$h) ){
        echo "图片比水印小，无法生成";
        return;
    }
    switch($waterPos){
        case 0://随机
            $posX = rand(0,($ground_w - $w));
            $posY = rand(0,($ground_h - $h));
            break;
        case 1://1为顶端居左
            $posX = 0;
            $posY = 0;
            break;
        case 2://2为顶端居中
            $posX = ($ground_w - $w) / 2;
            $posY = 0;
            break;
        case 3://3为顶端居右
            $posX = $ground_w - $w;
            $posY = 0;
            break;
        case 4://4为中部居左
            $posX = 0;
            $posY = ($ground_h - $h) / 2;
            break;
        case 5://5为中部居中
            $posX = ($ground_w - $w) / 2;
            $posY = ($ground_h - $h) / 2;
            break;
        case 6://6为中部居右
            $posX = $ground_w - $w;
            $posY = ($ground_h - $h) / 2;
            break;
        case 7://7为底端居左
            $posX = 0;
            $posY = $ground_h - $h;
            break;
        case 8://8为底端居中
            $posX = ($ground_w - $w) / 2;
            $posY = $ground_h - $h;
            break;
        case 9://9为底端居右
            $posX = $ground_w - $w;
            $posY = $ground_h - $h;
            break;
        default://随机
            $posX = rand(0,($ground_w - $w));
            $posY = rand(0,($ground_h - $h));
            break;
    }

//设定图像的混色模式
    imagealphablending($ground_im, true);

    if($isWaterImage){
//图片水印
        imagecopy($ground_im, $water_im, $posX, $posY, 0, 0, $water_w,$water_h);//拷贝水印到目标文件
    }
    else{//文字水印
        if( !empty($textColor)&&(strlen($textColor)==7)){
            $R = hexdec(substr($textColor,1,2));
            $G = hexdec(substr($textColor,3,2));
            $B = hexdec(substr($textColor,5));
        }
        else{
            die("水印文字颜色格式不正确！");
        }
        imagestring ( $ground_im, $textFont, $posX, $posY, $waterText, imagecolorallocate($ground_im, $R, $G, $B));
    }

//生成水印后的图片
    if($delete)@unlink($groundImage);
    if($newFileNAme=='')$newFileNAme=$groundImage.'.png';
    switch($ground_info[2]){//取得背景图片的格式
        case 1:imagegif($ground_im,$newFileNAme);break;
        case 2:imagejpeg($ground_im,$newFileNAme);break;
        case 3:imagepng($ground_im,$newFileNAme);break;
        default:die("暂时不支持该图片格式");
    }

//释放内存
    if(isset($water_info))unset($water_info);
    if(isset($water_im))imagedestroy($water_im);
    unset($ground_info);
    imagedestroy($ground_im);
}

//图片水印
imageWaterMark("../1.png","../2.png","../new.png");

echo "<img src='../new.png' />";

