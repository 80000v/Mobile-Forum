<?php
/*
 * 核心函数
 *
 */


/**
 * @param string $name
 * @return null|array|string
 * 配置读取函数
 */
function config($name)
{
    static $config = null;
    if (is_null($config)) {
        $config = include path_config . 'base.config.php';
    }
    return isset($config[$name]) ? $config[$name] : null;
}


/**
 * @param bool|null|string $var
 * @param bool|null|string $value
 * @return null|string
 * session处理函数
 */
function session($var = false, $value = false)
{
    //销毁session
    if (is_null($var)) {
        session_unset();//清除内存中的
        session_destroy();
        return;
    }
    //删除session
    if (is_null($value)) {
        unset($_SESSION[$var]);
        return;
    }
    //获取所有session
    if ($var === false) {
        return $_SESSION;
    }
    //获取单个session
    if ($value === false) {
        return isset($_SESSION[$var]) ? $_SESSION[$var] : null;
    }
    //设置session
    $_SESSION[$var] = $value;
}


/**
 * @param bool|false|string $var
 * @param bool|false|string $value
 * @param int $lifetime
 * @return null|void|string
 * cookie设置
 */
function cookie($var = false, $value = false, $lifetime = 3600)
{
    //删除所有的cookie
    if (is_null($var)) {
        foreach ($_COOKIE as $k => $v) {
            unset($_COOKIE[$k]);
            setcookie($k, '', time() - 3600);
        }
        return;
    }
    //删除指定cookie
    if (is_null($value)) {
        unset($_COOKIE[$var]);
        setcookie($var, '', time() - 3600);
        return;
    }

    //获取所有cookie
    if ($var === false) {
        return $_COOKIE;
    }

    //获取cookie
    if ($value === false) {
        return isset($_COOKIE[$var]) ? $_COOKIE[$var] : null;
    }
    //设置cookie
    $_COOKIE[$var] = $value;
    setcookie($var, $value, time() + $lifetime);
}

/**
 * @param string $name
 * @param string|int $default
 * @return string|int
 * 获取get,post数据，php5.3以下还包括cookie
 */
function input($name, $default = '')
{
    //存在并且不为空
    if (isset($_REQUEST[$name]) && !empty($_REQUEST[$name])) {
        return db_str_escape(htmlspecialchars(trim($_REQUEST[$name])));
    }
    return $default;
}


/**
 * @param string $info
 * @param string $url
 * @param array $other
 * 执行成功返回结果函数
 */
function success($info, $url = '', $other = array())
{
    $data = array();
    $data['status'] = 1;
    $data['info'] = $info;
    if (!empty($url)) $data['url'] = $url;
    $data = array_merge($data, $other);
    if (is_ajax || is_post) {
        header('Content-Type:application/json; charset=utf-8');
        exit(json_encode($data));
    } else {
        global $start_time;
        $title = $info;
        include './view/notice.html';
        exit;
    }
}

/**
 * @param string $info
 * @param string $url
 * @param array $other
 * 执行失败返回结果函数
 */
function error($info, $url = '', $other = array())
{
    $data = array();
    $data['status'] = 0;
    $data['info'] = $info;
    if (!empty($url)) $data['url'] = $url;
    $data = array_merge($data, $other);
    if (is_ajax || is_post) {

        header('Content-Type:application/json; charset=utf-8');
        exit(json_encode($data));
    } else {
        global $start_time;
        $title = $info;
        include './view/notice.html';
        exit;
    }
}


/**
 * @param string $url
 * @param int $time
 * @param string $msg
 * 重定向函数
 */
function redirect($url, $time = 0, $msg = '')
{
    if (!headers_sent()) {
        $time == 0 ? header("Location:" . $url) : header("refresh:{$time};url={$url}");
    } else {
        echo "<meta http-equiv='Refresh' content='{$time};URL={$url}'>";
    }
    if ($time) exit($msg);
    exit;
}
