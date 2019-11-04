<?php

namespace App\home;

use \Framework\Library\Process\Tool;

/**
 * 默认首页控制器
 * Class Index
 * @package App\Home
 */
class Index
{
    /**
     * 默认首页
     * @return mixed
     */
    public function index()
    {
        $openId = Tool::Receive('post.openid', '');
        $userName = Tool::Receive('post.username', '');
        $icon = Tool::Receive('post.icon', '');
        if (!empty($openId)) {
            $user = Db('wx_user_data')->select([
                'where' => ['openid' => $openId]
            ])->find();
            if (!$user) {
                //图片处理
                $insert = Db('wx_user_data')->insert([
                    'openid' => $openId,
                    'username' => $userName,
                    'icon' => $icon,
                    'icon_operate_count' => 0,
                    'created_at' => time(),
                    'updated_at' => time()
                ]);
            }
        }
        return true;
    }

    /**
     * 计数
     */
    public function count()
    {
        $user = Db('wx_user_data')->query("select count(id) as count FROM wx_user_data")->find();
        return $user["count"] ?: 0;
    }

    /**
     * 操作图片
     */
    public function iconOperate()
    {
        $openId = Tool::Receive('post.openid', '');
        if (!empty($openId)) {
            $user = Db('wx_user_data')->select([
                'where' => ['openid' => $openId,]
            ])->find();
            if ($user) {
                $update = Db('wx_user_data')->update(
                    ['icon_operate_count' => ++$user["icon_operate_count"], 'updated_at' => time()],
                    ['openid' => $openId]);
            }
        }
        return true;
    }

    public function uploadIcon($img)
    {
        $bases_size = 3; //图片大小，可动态限制,默认为3M
        $base64_image = str_replace(' ', '+', $img);
        //post方式接收的数据, 加号会被替换为空格, 需要重新替换回来, 若不是post数据, 不需要执行
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image, $result)) {
            //判断图片后缀
            $pic_ars = array('jpg', 'jpeg', 'png');
            if (!in_array($result[2], $pic_ars)) {
                exit(json_encode(array('status' => 0, 'message' => '文件格式有误')));
            } else {
                //定义文件名称
                $picname = uniqid(); //这里会重复上传图片，如果不允许的话，可以给定特殊文件名称来判断,比如用户名
                $picdir = $picname . '.' . $result[2];
            }
            //判断大小
            $size_format = ceil((strlen($img) - ((strlen($img) / 8) * 2)) / 1024);
            if ($size_format > $bases_size * 1024) {
                exit(json_encode(array('status' => 0, 'message' => '图片大小不能超过3M')));
            }
            //定义图片储存文件目录
            $dir = $_SERVER['DOCUMENT_ROOT'] . "/upload/icon/" . date('Ymd');
            if (!is_dir($dir)) {
                //如果不存在就创建该目录
                mkdir($dir, 0777, true);
            }
            //图片名称
            $image_url = $dir . '/' . $picdir;
            //储存图片
            $img_url = '';//图片路径
            if (file_put_contents($image_url, base64_decode(str_replace($result[1], '', $base64_image)))) {
                $img_url = "upload/" . $picdir;
            }
            return $img_url;
        }
    }

    /**
     * @param $pic_path 外框图片
     * @param $bg_path 头像图片
     * @param $save_path 保存图片的绝对路径
     */
    public function imgMix($pic_path, $bg_path, $save_path)
    {
        $bg_w = 600; // 背景图片宽度
        $bg_h = 600; // 背景图片高度
        $background = $this->resize_image($bg_path, $bg_path, $bg_w, $bg_h);
        $color = imagecolorallocate($background, 202, 201, 201); // 为真彩色画布创建白色背景，再设置为透明
        imagefill($background, 0, 0, $color);
        imageColorTransparent($background, $color);
        $start_x = 0; // 开始位置X
        $start_y = 0; // 开始位置Y
        $pic_w = intval($bg_w); // 宽度
        $pic_h = intval($bg_h); // 高度
        $pathInfo = pathinfo($pic_path);
        switch( strtolower($pathInfo['extension']) ) {
            case 'jpg':
            case 'jpeg':
                $imagecreatefromjpeg = 'imagecreatefromjpeg';
                break;
            case 'png':
                $imagecreatefromjpeg = 'imagecreatefrompng';
                break;
            case 'gif':
            default:
                $imagecreatefromjpeg = 'imagecreatefromstring';
                $pic_path = file_get_contents($pic_path);
                break;
        }
        $resource = $imagecreatefromjpeg($pic_path);
        imagecopyresized($background,$resource,$start_x,$start_y,0,0,$pic_w,$pic_h,imagesx($resource),imagesy($resource)); // 最后两个参数为原始图片宽度和高度，倒数两个参数为copy时的图片宽度和高度

        imagejpeg($background);
        return imagepng($background, $save_path.time().".png");
    }

    // 重置图片文件大小
    public function resize_image($filename, $tmpname, $xmax, $ymax)
    {
        $ext = explode(".", $filename);
        $ext = $ext[count($ext)-1];

        if($ext == "jpg" || $ext == "jpeg")
            $im = imagecreatefromjpeg($tmpname);
        elseif($ext == "png")
            $im = imagecreatefrompng($tmpname);
        elseif($ext == "gif")
            $im = imagecreatefromgif($tmpname);

        $x = imagesx($im);
        $y = imagesy($im);

        if($x <= $xmax && $y <= $ymax)
            return $im;

        if($x >= $y) {
            $newx = $xmax;
            $newy = $newx * $y / $x;
        }
        else {
            $newy = $ymax;
            $newx = $x / $y * $newy;
        }

        $im2 = imagecreatetruecolor($newx, $newy);
        imagecopyresized($im2, $im, 0, 0, 0, 0, floor($newx), floor($newy), $x, $y);
        return $im2;
    }
}