<?php
defined('shouing') or die('Access is denied.');
/**
 * 管理员控制器
 */
$action = input(1);

if ($action == 'login') {
    /**
     * 管理员登录
     */
    if (is_post || is_ajax) {
        $username = input('username');
        $password = input('password');

        $sql = sprintf('select * from wlz_admin where username="%s" and password="%s"', $username, md5($password));
        $admin = db_find($sql) or error('账号或密码错误');

        session('admin_uid', $admin['id']);
        success('登录成功', 'index.html');
    } else {
        $title = '管理员登录';
        include './view/admin_login.html';
    }
} else if ($action == 'logout') {

    session('admin_uid', null);
    redirect('admin-login.html', 3, '退出成功');

} else if ($action == 'password') {
    /**
     * 密码修改 测试通过
     */
    if (is_post || is_ajax) {
        $old_password = md5(input('password_old'));
        $new_password = input('password_new');

        preg_match('#^\w{6,16}$#', $new_password) or error('新密码为长度6-16位数字字母下划线组成');
        db_field('select password from wlz_admin') != $old_password and error('原密码不正确','admin-password.html');

        //更新密码
        $sql = sprintf('update wlz_admin set password="%s"', md5($new_password));
        if(db_query('update wlz_admin set password="'.md5($new_password).'"')){
            success('修改密码成功', 'index.html');
        }else{
            error('修改密码失败','index.html');
        }

    } else {
        $title = '后台密码修改';
        include './view/admin_password.html';
    }


} else {
    error('功能未实现');
}