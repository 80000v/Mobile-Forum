<?php
/*
 * 用户管理
 */
defined('shouing') or die('Access is denied.');
$action = input(1);
if ($action == 'modify') {
    /**
     * 状态修改
     */
    $uid = input(2);
    if (is_post || is_ajax) {

        $data = array(
            'nickname' => input('nickname'),
            'email' => input('email'),
            'phone' => input('phone'),
            'sex' => intval(input('sex')),
            'info' => input('info'),
            'status' => intval(input('status'))
        );

        //昵称判断
        $len = mb_strlen($data['nickname'], 'utf-8');
        ($len < 2 || $len > 20) and error('昵称长度为2-20个字');
        $sql = sprintf('select id from wlz_user nickname="%s" and id!="%s"', $data['nickname'], $uid);
        db_find($sql) and error('昵称已经被其他人使用');
        //邮箱验证
        preg_match('#^\w+(\.\w+)*?@\w+?(\.\w+)+?$#', $data['email']) or error('邮箱格式不正确');
        $sql = sprintf('select id from wlz_user email="%s" and id!="%s"', $data['email'], $uid);
        db_find($sql) and error('邮箱绑定了其它的账号');
        //手机号
        preg_match('#^1[23578]\d{9}|$#', $data['phone']) or error('手机号格式不正确');
        $sql = sprintf('select id from wlz_user phone="%s" and id!="%s"', $data['phone'], $uid);
        db_find($sql) and error('手机号绑定了其它的账号');
        //性别验证
        in_array($data['sex'], array(0, 1, 2)) or error('性别设置有误');
        //状态严重
        in_array($data['status'], array(0, 1, 2)) or error('状态设置有误');

        $sql = sprintf('update wlz_user set %s where id=%s', db_str_concat($data), $uid);
        if (db_query($sql)) {
            success('修改成功', 'user.html');
        } else {
            error('修改失败');
        }

    } else {
        $title = '用户修改';
        $user = db_find('select * from wlz_user where id="' . $uid . '"') or error('用户不存在');
        include './view/user_modify.html';
    }

} else {
    /**
     * 用户列表
     */
    $page = $action;
    $count = db_field('select count(*) from wlz_user');
    $page_show = pages('user-%s.html', $count, $page, config('page_num'), $limit);
    $users = db_select('select * from wlz_user order by create_time desc limit ' . $limit);
    foreach ($users as $k => $v) {
        $users[$k]['status'] = format_user_status($v['status']);
    }
    $title = '用户管理';
    include './view/user.html';
}