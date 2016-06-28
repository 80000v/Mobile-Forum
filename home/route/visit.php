<?php
defined('shouing') or error('Access is denied.');

if (session('uid')) {
    if (is_ajax || is_post) {
        success('你已经是登录状态', 'user.html');
    } else {
        redirect('user.html');
    }
}
$action = input(1, 'login');

if ($action == 'login') {
    if (is_post || is_ajax) {

        //接收并调整数据
        $username = input('username');
        $password = md5(input('password'));

        //登录判断
        $sql = sprintf('select id,status from wlz_user where username="%s" and password="%s"', $username, $password);
        $user = db_find($sql) or error('账号或密码错误');
        $user['status'] == 1 and error('账号被冻结，请联系管理员');
        $user['status'] == 2 and error('账号还未进行审核');

        //更新信息
        $data = array(
            'login_time' => time(),
            'login_ip' => get_client_ip()
        );
        db_query(sprintf('update wlz_user set %s where id="%s"', db_str_concat($data), $user['id']));

        //设置用户唯一标识session
        session('uid', $user['id']);
        session('username', $username);

        //设置cookie用于自动登录
        cookie('username', $username, time() + 3600 * 24 * 7);
        cookie('password', $password, time() + 3600 * 24 * 7);

        success('登录成功', 'index.html');

    } else {
        $title = '用户登录';
        include './view/login.html';
    }

} else if ($action == 'register') {

    if (is_post || is_ajax) {

        strtoupper(input('code')) != session('code') and error("验证码错误");
        $data = array(
            'username' => input('username'),
            'password' => input('password'),
            'nickname' => input('nickname'),
            'email' => input('email'),
            'create_time' => time(),
            'login_time' => time(),
            'login_ip' => get_client_ip()
        );
        //用户名验证
        preg_match('#^\w{2,20}$#', $data['username']) or error('账号为长度2-20位数字字母下划线组成');
        $sql = 'select id from wlz_user wherer username="' . $data['username'] . '"';
        db_find($sql) and error('用户名已经存在');
        //密码验证
        preg_match('#^\w{6,16}$#', $data['password']) or error('密码为长度6-16位数字字母下划线组成');
        //昵称验证
        $len = mb_strlen($data['nickname'], 'utf-8');
        ($len < 2 || $len > 20) and error('昵称长度为2-20个字');
        $sql = 'select id from wlz_user where nickname="' . $data['nickname'] . '"';
        db_find($sql) and error('昵称已经被其他人使用');
        //邮箱验证
        preg_match('#^\w+(\.\w+)*?@\w+?(\.\w+)+?$#', $data['email']) or error('邮箱格式不正确');
        $sql = 'select id from wlz_user where email="' . $data['email'] . '"';
        db_find($sql) and error('邮箱绑定了其它的账号');

        //插入记录
        $data['password'] = md5($data['password']);
        if (db_insert('wlz_user', $data)) {
            //毁掉验证码，防止重复注册
            session('code', mt_rand());
            success('注册成功', 'visit-login.html');
        } else {
            error('注册失败');
        }

    } else {
        $title = '用户注册';
        include './view/register.html';
    }

} else if ($action == 'findpwd') {
    if (is_post || is_ajax) {
        strtoupper(input('code')) != session('code') and error("验证码错误");
        $email = input('email');
        $user = db_find('select username,password,email from wlz_user where email="' . $email . '"') or error('邮箱号不存在');
        //找回密码的业务逻辑
        $url = 'http://' . config('site_url') . '/visit-resetpwd-' . $user['username'] . '-' . $user['password'] . '.html';
        if (send_email($email, '找回密码', '点击链接进入设置新密码：<a href="' . $url . '">' . $url . '</a>')) {
            //成功之后的逻辑
            session('code', mt_rand());
            success('新的密码已经发到你的邮箱中', 'visit-login.html');
        } else {
            error('网站功能暂时不支持,请联系站长');
        }

    } else {
        $title = '找回密码';
        include './view/findpwd.html';
    }
} else if ($action == 'resetpwd') {
    /**
     * 重置密码
     */
    $username = input(2);
    $password = input(3);
    $sql = sprintf('select id from wlz_user where username="%s" and password="%s"', $username, $password);
    db_find($sql) or error('链接失效');
    if (is_post || is_ajax) {
        $password_new = input('password_new');

        //密码验证
        preg_match('#^\w{6,16}$#', $password_new) or error('密码为长度6-16位数字字母下划线组成');

        $sql = sprintf('update wlz_user set password="%s" where username="%s"', md5($password_new), $username);
        if (db_query($sql)) {
            success('重置密码成功', 'visit-login.html');
        } else {
            error('重置密码失败');
        }

    } else {

        $title = '重置密码';
        include './view/resetpwd.html';
    }

}