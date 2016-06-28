<?php
/*
 *
 * 网站主应用 home入口文件
 * time : 2016.4.16
 * author 万林赞
 */
include '../init.php';

/**
 * 网站根目录的网址
 */
define('url_root', './');

/**
 * upload 文件夹的网址,用户头像、版块图标
 */
define('url_upload', url_root . 'upload/');
define('url_upload_usericon', url_upload . 'usericon/');
define('url_upload_sectionicon', url_upload . 'sectionicon/');
define('url_upload_images', url_upload . 'images/');
define('url_upload_attachment', url_upload . 'attachment/');

/**
 * images 文件夹的网址
 */
define('url_images', url_root . 'images/');

/**
 * static 文件夹的网址
 */
define('url_static', url_root . 'static/');

/**
 * 前一个页面的网址
 */
define('url_refer', isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null);

/**
 * 当前页面的网址
 */
define('url_current', $_SERVER['REQUEST_URI']);


//自动登录
if (!session('uid')) {
    $username = cookie('username');
    $password = cookie('password');

    $user = db_find('select * from wlz_user where username="' . $username . '" and password="' . $password . '"');
    if ($user && $user['status'] == 0) {
        //设置用户唯一标识session
        session('uid', $user['id']);
        session('username', $username);

        //设置cookie用于自动登录
        cookie('username', $username, time() + 3600 * 24 * 7);
        cookie('password', $password, time() + 3600 * 24 * 7);
    }
}

//-------------------------------------------------

$route = input(0, 'index');

$filename = './route/' . $route . '.php';

if (!file_exists($filename)) {
    error('功能未实现');
}

include $filename;