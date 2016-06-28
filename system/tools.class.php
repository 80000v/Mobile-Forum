<?php
/*
 * 工具函数类
 * 万林赞 2016.4.16
 */


/**
 * @param bool $type
 * @return int|string
 * 获取用户的ip地址,默认返回字符串类型，否则返回数字类型
 */
function get_client_ip($type = false)
{
    if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
        $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
    } else if (isset($_SERVER["HTTP_CLIENT_IP"])) {
        $ip = $_SERVER["HTTP_CLIENT_IP"];
    } else {
        $ip = $_SERVER["REMOTE_ADDR"];
    }
    return $type ? ip2long($ip) : $ip;
}


/**
 * @param int $size
 * @param int $decimals
 * @return string
 * 转化为人能看懂的大小单位
 */
function get_size($size, $decimals = 2)
{
    switch (true) {
        case $size >= pow(1024, 3):
            return round($size / pow(1024, 3), $decimals) . " GB";
        case $size >= pow(1024, 2):
            return round($size / pow(1024, 2), $decimals) . " MB";
        case $size >= pow(1024, 1):
            return round($size / pow(1024, 1), $decimals) . " KB";
        default:
            return $size . 'B';
    }
}


//返回uuid
function uuid()
{
    return md5(uniqid(mt_rand(), true));
}


/**
 * @param $str
 * @return mixed
 * 分析字符串中的图片名
 */
function get_str_images($str)
{
    preg_match_all('#\[img=(.*?)\]#', $str, $match);
    return array_map('basename', $match[1]);
}

/**
 * @param $new_images
 * @param array $old_images
 * 处理内容中的图片
 */
function images_handler($new_images, $old_images = array())
{
    //数组的交集
    $intersect = array_intersect($new_images, $old_images);
    //缺少的images
    $lack = array_diff($old_images, $intersect);
    //新增的images
    $add = array_diff($new_images, $intersect);
    //处理减少的images
    foreach ($lack as $v) {
        db_transaction();
        $result = db_find('select num from wlz_images where name="' . $v . '" for update');
        if ($result && $result['num'] > 1) {
            db_query('update wlz_images set num=num-1 where name="' . $v . '"');
        } else if ($result) {
            if (db_query('delete from wlz_images where name="' . $v . '"')) {
                is_file(path_upload_images . $v) and unlink(path_upload_images . $v);
            }
        } else {
            is_file(path_upload_images . $v) and unlink(path_upload_images . $v);
        }
        db_commit();
    }
    //处理增加的images
    foreach ($add as $v) {
        if (is_file(path_upload_images . $v)) {
            db_query('update wlz_images set num=num+1 where name="' . $v . '"');
        } else if (is_file(path_upload_temp . $v)) {
            if (db_insert('wlz_images', array(
                'name' => $v,
                'num' => 1
            ))) {
                rename(path_upload_temp . $v, path_upload_images . $v);
            }
        }
    }
}


/**
 * @param $file
 * @param $path
 * @param null $ext
 * @param null $size
 * @param string $new_name 新文件名
 * @return array
 * @throws Exception
 * 只支持单文件上传
 */
function upload_file($file, $path, $ext = null, $size = null)
{
    if (!file_exists($path)) {
        mkdir($path, 0700, true);
    }
    if (is_null($ext)) $ext = array();
    if (is_null($size)) $size = config('upload_size');

    $error = array(
        UPLOAD_ERR_INI_SIZE => '上传文件超过PHP.INI配置文件允许的大小',
        UPLOAD_ERR_FORM_SIZE => '文件超过表单限制大小',
        UPLOAD_ERR_PARTIAL => '文件只有部分上传',
        UPLOAD_ERR_NO_FILE => '没有文件被上传',
        UPLOAD_ERR_NO_TMP_DIR => '找不到临时文件夹',
        UPLOAD_ERR_CANT_WRITE => '文件写入失败'
    );

    if (array_key_exists($file['error'], $error)) {
        error($file['name'] . $error[$file['error']]);
    }

    //截取文件后缀并判断
    $temp_array = explode('.', $file['name']);
    $file['ext'] = strtolower(array_pop($temp_array));
    if (!in_array($file['ext'], explode('|', $ext))) {
        return array(false, $file['name'] . '文件后缀不允许上传');
    }

    //是图片类型，但是获取不到文件大小
    if (strstr(strtolower($file['type']), "image") && !getimagesize($file['tmp_name'])) {
        return array(false, $file['name'] . '不是一个合法图片');
    }

    if ($file['size'] > $size) {
        return array(false, $file['name'] . '文件大于' . get_size($size));
    }

    if (!is_uploaded_file($file['tmp_name'])) {
        return array(false, $file['name'] . '可能是非法文件');
    }

    //生成文件名
    $new_name = uuid() . '.' . $file['ext'];

    if (!move_uploaded_file($file['tmp_name'], $path . $new_name)) {
        return array(false, '文件上传失败');
    }

    $file = array('new_name' => $new_name, 'old_name' => $file['name'], 'size' => $file['size']);

    return array(true, $file);
}

/**
 * @param $pattern
 * @param $count
 * @param $current
 * @param $size
 * @param $limit
 * @return string
 * 页码显示 pages('section-1-%s.html',2000,5,10,$limit);
 */
function pages($pattern, $count, $current, $size, &$limit)
{
    $count = intval($count);
    $current = intval($current);
    $size = intval($size);

    //总页数
    $page = ceil($count / $size);
    //当前页修正 防止page超过范围
    $current = max(1, min($page, $current));
    $limit = $size * ($current - 1) . ',' . $size;

    //首页和上一页的按钮
    if ($current == 1) {
        $first = '<span>首页</span>';
        $pre = '<span>上一页</span>';
    } else {
        $first = '<a href="' . sprintf($pattern, 1) . '">首页</a>';
        $pre = '<a href="' . sprintf($pattern, $current - 1) . '">上一页</a>';
    }


    //详情的按钮和表单
    $info = '<span class="page-info"><input class="page-input" data-url="' . sprintf($pattern, '#') . '" type="text" value="' . $current . '"/>&nbsp;/&nbsp;' . $page . '</span>';

    if ($current < $page) {
        $next = '<a class="page-next" href="' . sprintf($pattern, $current + 1) . '">下一页</a>';
    } else {
        $next = '<span class="page-next">下一页</span>';
    }

    return $first . $pre . $info . $next;
}

/**
 * @param $image
 * @param null $width
 * @param null $height
 * 图像缩放
 */
function images_zoom($image, $width = null, $height = null)
{
    $ext = explode('/', image_type_to_mime_type(exif_imagetype($image)));
    $func_name = 'imagecreatefrom' . end($ext);
    $dst = $func_name($image);
    //图片的高度
    $old_width = imagesx($dst);
    $old_height = imagesy($dst);
    $width = $width ? $width : $old_width;
    $height = $height ? $height : $old_height;
    //复制到新图像
    $img = imagecreatetruecolor($width, $height);
    $alpha = imagecolorallocatealpha($img, 0, 0, 0, 127);
    imagefill($img, 0, 0, $alpha);
    imagecopyresampled($img, $dst, 0, 0, 0, 0, $width, $height, $old_width, $old_height);
    imagesavealpha($img, true);//
    imagepng($img, $image);
    imagedestroy($dst);
    imagedestroy($img);
}

function send_email($to, $title, $content)
{
    require path_root . 'mailer/PHPMailerAutoload.php';
    $mail = new PHPMailer();
    $mail->isSMTP();
    $mail->SMTPAuth = true;
    $mail->Host = 'smtp.qq.com';
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;
    $mail->CharSet = 'UTF-8';
    $mail->FromName = config('site_name') . config('site_url');
    $mail->Username = config('email_name');
    $mail->Password = config('email_pwd');//'tbyduuggtxrybedj';
    //发件人地址
    $mail->From = config('email');
    $mail->isHTML(true);
    //设置收件人邮箱地址
    $mail->addAddress($to);
    //邮件的标题
    $mail->Subject = $title;
    //邮件正文
    $mail->Body = $content;
    return $mail->send();
}