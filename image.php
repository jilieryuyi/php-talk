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

    protected static function getSource($file)
    {
        $size = getimagesize($file);
        switch($size[2]){
            case 1: return imagecreatefromgif($file);break;
            case 2: return imagecreatefromjpeg($file);break;
            case 3: return imagecreatefrompng($file);break;
            default:
                return null;
        }
    }

    /**
     * 设置图片水印
     */
    public function setImageMark($water_file, $params = [
        "pos_x"      => 0,         //x轴位置
        "pos_y"      => 0,         //y轴位置
        "safe_mode"  => true,      //安全模式，不破坏原有图片
        "handle"     => "save",    //save 或者 output 保存或者输出
        "save_path"  => ""         //只有当handle为save时有效
    ])
    {

        $default_params = [
            "pos_x"      => 0,         //x轴位置
            "pos_y"      => 0,         //y轴位置
            "safe_mode"  => true,       //安全模式，不破坏原有图片
            "handle"     => "save",    //save 或者 output 保存或者输出
            "save_path"  => ""
        ];
        $params = array_merge($default_params, $params);

        $ground_im = self::getSource($this->image_filename);

        if (!$ground_im) {
            return null;
        }

        $water_im  = self::getSource($water_file);
        if (!$water_im) {
            return null;
        }

        $water_image  = new self($water_file);
        $water_width  = $water_image->getWidth();
        $water_height = $water_image->getHeight();

        $ground_width  = $this->getWidth();
        $ground_height = $this->getHeight();

        $max_x = $ground_width - $water_width;
        $max_y = $ground_height - $water_height;

        $params["pos_x"] = $params["pos_x"] > $max_x ? $max_x : $params["pos_x"];
        $params["pos_y"] = $params["pos_y"] > $max_y ? $max_y : $params["pos_y"];

        imagecopy($ground_im, $water_im,
            $params["pos_x"], $params["pos_y"], 0, 0,
            $water_width, $water_height);//拷贝水印到目标文件

        //设定图像的混色模式
        imagealphablending($ground_im, true);
        $save_path = $params["save_path"];

        if ("save" == $params["handle"]) {
            //自动生成保存路径
            if (!$save_path) {
                $path_info = pathinfo($this->image_filename);
                $save_path = $path_info["dirname"] . "/" . $path_info["filename"] . "-" . time() . rand(100000, 999999) . ".mark.png";
            }
            if ($params["safe_mode"] && file_exists($save_path)) {
                echo "警告：文件已存在" . $save_path;
                return null;
            }

            //header("Content-type: image/png");
            //覆盖写入
            imagepng($ground_im, $save_path);
            imagedestroy($ground_im);
            imagedestroy($water_im);
            return $save_path;
        } else {
            header("Content-type: image/png");
            imagepng($ground_im);
            imagedestroy($ground_im);
            imagedestroy($water_im);
            return null;
        }
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
        $im = self::getSource($this->image_filename);
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
            "handle"     => "save",    //save 或者 output 保存或者输出
            "save_path"  => ""         //只有当handle为save时有效
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
            "handle"     => "save",    //save 或者 output 保存或者输出
            "save_path"  => ""
        ];
        $params = array_merge($default_params, $params);
        $font_file = self::getFont($params["font"]);


        //得到文字的大小 这样才能准确判定文字水印可以写入的坐标范围
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

        $ground_image_source = self::getSource($this->image_filename);
        $R = hexdec(substr($params["text_color"],1,2));
        $G = hexdec(substr($params["text_color"],3,2));
        $B = hexdec(substr($params["text_color"],5));
        //imagestring($ground_image_source, $params["text_font"],
//        $params["pos_x"], $params["pos_y"], $water_text,
//            imagecolorallocate($ground_image_source, $R, $G, $B)
//        );


        //将文字写入到图片
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


        //设定图像的混色模式
        imagealphablending($ground_image_source, true);
        $save_path = $params["save_path"];

        if ("save" == $params["handle"]) {
            //自动生成保存路径
            if (!$save_path) {
                $path_info = pathinfo($this->image_filename);
                $save_path = $path_info["dirname"] . "/" . $path_info["filename"] . "-" . time() . rand(100000, 999999) . ".mark.png";
            }
            if ($params["safe_mode"] && file_exists($save_path)) {
                echo "警告：文件已存在" . $save_path;
                return null;
            }

            //header("Content-type: image/png");
            //覆盖写入
            imagepng($ground_image_source, $save_path);
            imagedestroy($ground_image_source);
            return $save_path;
        } else {
            header("Content-type: image/png");
            imagepng($ground_image_source);
            imagedestroy($ground_image_source);
            return null;
        }
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
$img->setImageMark(__DIR__."/m.png", ["mode"=>false, "handle" => "save"]);
//$img->toPng("", true);


