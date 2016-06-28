<?php
/*
 * 后台入口文件
 *
 * 2016.4.21
 */

include '../init.php';

/**
 * 网站根目录的网址
 */
define('url_root', '../');

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

//--------------------------------------------------------------

//路由
$route = input(0, 'index');

//对登录情况做处理
if(!session('admin_uid')){
    //未登录必须的进入admin-login.html页面
    if($route != 'admin' || input(1) != 'login'){
        if(is_ajax || is_post){
            error('你还没有登录','admin-login.html');
        }else{
            redirect('admin-login.html');
        }
    }
}else{
    //登录之后不能进入admin-login.html页面
    if($route == 'admin' && input(1) == 'login'){
        if(is_ajax || is_post){
            success('登录成功','index.html');
        }else{
            redirect('index.html');
        }
    }
}

$filename = './route/' . $route . '.php';
if (!file_exists($filename)) {
    error('功能未实现');
}
include $filename;
