<?php

include_once __DIR__ . "/base/WxBase.class.php";

/**
 * 微信媒体素材管理
 * @link http://mp.weixin.qq.com/wiki/5/963fc70b80dc75483a271298a76a8d59.html
 */
class MediaHandler extends WxBase
{
    /**
     * 下载多媒体文件
     * @param string $media_id 媒体文件ID
     * @param string $path 存储路径（可带斜杠可不带）
     * @param string $filename 存储名称（不包括后缀）
     * @return string 成功返回路径
     */
    public function get($media_id, $path, $filename = '')
    {
        $param = array('access_token' => $this->get_token(), 'media_id' => $media_id);

        $fileInfo = $this->download(self::URL . "media/get", $param);
        if ($fileInfo) {
            if (!$filename) $filename = $fileInfo['filename'];
            else {
                $suffix = pathinfo($fileInfo['filename'],PATHINFO_EXTENSION );
                $filename = $filename . '.' . $suffix;
            }
            $filename = rtrim($path, '/') . '/' . $filename;

            $res = $this->save($filename, $fileInfo['body']);
            if ($res) return $filename;

            $this->set_error('本地', __CLASS__ . ' -> ' . __FUNCTION__, '文件（' . $fileInfo['filename'] . '）保存失败');
            return false;
        }
        return false;
    }

    /**
     * @param string $url 请求地址
     * @param string|array $param 请求参数
     * @return array
     */
    private function download($url, $param)
    {
        $ch = curl_init($url . "?" . http_build_query($param));
        curl_setopt($ch, CURLOPT_HEADER, 1);    //取header头
        curl_setopt($ch, CURLOPT_NOBODY, 0);    //取body头
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);

        //分离header与body
        if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == '200') {
            $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE); //头信息size
            $header = substr($response, 0, $headerSize);
            $body = substr($response, $headerSize);
            if (curl_getinfo($ch, CURLINFO_CONTENT_TYPE) == 'text/plain') {
                curl_close($ch);
                $res = json_decode($body, true);
                $this->set_error($url, $res['errcode']);
                return false;
            }
            curl_close($ch);
            $arr = array();
            $filename = null;
            if (preg_match('/filename="(.*?)"/', $header, $arr)) {
                $filename = $arr[1];
            }
            $file = array_merge(array('filename' => $filename), array('body' => $body));
            return $file;
        }else{
            curl_close($ch);
            $this->set_error($url, -1);
            return false;
        }
    }

    /**
     * 保存为文件
     * @param string $fullName 文件名包括路径）
     * @param string $filecontent 文件内容
     * @return bool
     */
    private function save($fullName, $filecontent)
    {
        $dir = dirname($fullName);
        if (!file_exists($dir)) {
            @mkdir($dir, 0777, true);
            @chmod($dir, 0777);
        }
        return !!file_put_contents($fullName, $filecontent);
    }

    /**
     * @param string $image 原图
     * @param string $thumbname 缩略图文件名
     * @param string $type 图像格式
     * @param int $maxWidth 宽度
     * @param int $maxHeight 高度
     * @param bool|true $interlace 启用隔行扫描
     * @return bool
     */
    public function thumb($image,$thumbname,$type='',$maxWidth='',$maxHeight='',$interlace=true)
    {
        // 获取原图信息
        $info = $this->getImageInfo($image);
        if ($info !== false) {
            $srcWidth = $info['width'];
            $srcHeight = $info['height'];
            $type = empty($type) ? $info['type'] : $type;
            $type = strtolower($type);
            $interlace = $interlace ? 1 : 0;
            unset($info);
            if (!$maxWidth) $maxWidth = $srcWidth;
            if (!$maxHeight) $maxHeight = $srcHeight;
            $scale = min($maxWidth / $srcWidth, $maxHeight / $srcHeight); // 计算缩放比例
            if ($scale >= 1) {
                // 超过原图大小不再缩略
                $width = $srcWidth;
                $height = $srcHeight;
            } else {
                // 缩略图尺寸
                $width = (int)($srcWidth * $scale);
                $height = (int)($srcHeight * $scale);
            }

            // 载入原图
            $createFun = 'ImageCreateFrom' . ($type == 'jpg' ? 'jpeg' : $type);
            $srcImg = $createFun($image);

            //创建缩略图
            if ($type != 'gif' && function_exists('imagecreatetruecolor'))
                $thumbImg = imagecreatetruecolor($width, $height);
            else
                $thumbImg = imagecreate($width, $height);

            // 复制图片
            if (function_exists("ImageCopyResampled"))
                imagecopyresampled($thumbImg, $srcImg, 0, 0, 0, 0, $width, $height, $srcWidth, $srcHeight);
            else
                imagecopyresized($thumbImg, $srcImg, 0, 0, 0, 0, $width, $height, $srcWidth, $srcHeight);
            if ('gif' == $type || 'png' == $type) {
                //imagealphablending($thumbImg, false);//取消默认的混色模式
                //imagesavealpha($thumbImg,true);//设定保存完整的 alpha 通道信息
                $background_color = imagecolorallocate($thumbImg, 0, 255, 0);  //  指派一个绿色
                imagecolortransparent($thumbImg, $background_color);  //  设置为透明色，若注释掉该行则输出绿色的图
            }

            // 对jpeg图形设置隔行扫描
            if ('jpg' == $type || 'jpeg' == $type) imageinterlace($thumbImg, $interlace);

            //$gray=ImageColorAllocate($thumbImg,255,0,0);
            //ImageString($thumbImg,2,5,5,"ThinkPHP",$gray);
            // 生成图片
            $imageFun = 'image' . ($type == 'jpg' ? 'jpeg' : $type);
            $imageFun($thumbImg, $thumbname);
            imagedestroy($thumbImg);
            imagedestroy($srcImg);
            return $thumbname;
        }
        return false;
    }

    /**
     * @param string $img 图像文件名
     * @return array|bool
     */
    function getImageInfo($img)
    {
        $imageInfo = getimagesize($img);
        if ($imageInfo !== false) {
            $imageType = strtolower(substr(image_type_to_extension($imageInfo[2]), 1));
            $imageSize = filesize($img);
            $info = array(
                "width" => $imageInfo[0],
                "height" => $imageInfo[1],
                "type" => $imageType,
                "size" => $imageSize,
                "mime" => $imageInfo['mime']
            );
            return $info;
        }
        return false;
    }
}