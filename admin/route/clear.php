<?php
defined('shouing') or die('Access is denied.');
$action = input(1);

if ($action == 'images') {
    /**
     * 清空垃圾图片
     */
    if ($dir = opendir(path_upload_temp)) {
        while (($filename = readdir($dir)) !== false) {
            if ($filename != '.' && $filename != '..') {
                $filename = path_upload_temp . $filename;
                //一个星期之前的文件
                if (filemtime($filename) + (3600 * 24 * 7) < time()) {
                    unlink($filename);
                }
            }
        }
        closedir($dir);
        success('清除图片成功', 'index.html');
    } else {
        error('清除图片失败', 'index.html');
    }


} else if ($action == 'message') {

    if (db_query('delete from wlz_message where create_time <' . (time() - 3600 * 24 * 7))) {
        success('清除消息成功', 'index.html');
    } else {
        error('清除消息失败', 'index.html');
    }

}