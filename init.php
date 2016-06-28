<?php
error_reporting(0);
define('shouing', '111');
header("Content-type:text/html;charset=utf-8");

/**
 * 网站根目录
 */
define('path_root', str_replace('\\', '/', dirname(__FILE__)) . '/');

/**
 * 网站配置文件目录
 */
define('path_config', path_root . 'config/');

/**
 * 系统函数文件目录
 */
define('path_system', path_root . 'system/');


/**
 * 系统图片文件目录
 * 前端用到的一些小图片：默认头像，版主图标，板斧图标...水印图片
 */
define('path_images', path_root . 'images/');

/**
 * 系统静态文件目录
 * 比如验证码字体文件，css、js、
 */
define('path_static', path_root . 'static/');

/**
 * 网站上传文件目录、用户头像目录、版块图标目录
 */
define('path_upload', path_root . 'upload/');
define('path_upload_usericon', path_upload . 'usericon/');
define('path_upload_sectionicon', path_upload . 'sectionicon/');
define('path_upload_images', path_upload . 'images/');
define('path_upload_attachment', path_upload . 'attachment/');
define('path_upload_temp', path_upload . 'temp/');//上传临时目录


/**
 * 加载系统函数文件
 */
include path_system . 'db.func.php';
include path_system . 'core.func.php';
include path_system . 'debug.func.php';
include path_system . 'tools.class.php';
include path_system . 'format.func.php';
include path_system . 'app.func.php';

/**
 * 定义系统环境常量
 */
define('is_get', $_SERVER['REQUEST_METHOD'] === 'GET' ? true : false);
define('is_post', $_SERVER['REQUEST_METHOD'] === 'POST' ? true : false);
define('is_ajax', isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');

/**
 * 设置时区
 */
date_default_timezone_set(config('time_zone'));

/**
 * 设置session的处理方式
 */
session_set_save_handler(function () {//打开
    return true;
}, function () {//关闭

    if (mt_rand(0, 100) == 10) {
        $sql = sprintf('delete from wlz_session where atime < %s', time() - config('session_lifetime'));
        return db_query($sql);
    }
    return true;

}, function ($id) {//读取

    $sql = sprintf("select data from wlz_session where id = '%s' and atime > %d", $id, time() - config('session_lifetime'));
    return db_field($sql);

}, function ($id, $data) {//写入

    $sql = sprintf("replace into wlz_session (id,data,atime,ip) values ('%s','%s',%d,%d)", $id, $data, time(), get_client_ip());
    return db_query($sql);

}, function ($id) {//卸载

    return db_query("delete from wlz_session where id = '{$id}'");

}, function () {//垃圾回收

    $sql = sprintf('delete from wlz_session where atime < %s', time() - config('session_lifetime'));
    return db_query($sql);

});

/**
 * 开启session
 */
session_start();

/**
 * 分析 url GET 参数,并且合并进$_REQUEST
 */
$_REQUEST += explode('-', substr($_SERVER['QUERY_STRING'], 0, -5));
parse_str(parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY), $get);
$_REQUEST += $get;

