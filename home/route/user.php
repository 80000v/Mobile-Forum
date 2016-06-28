<?php
/**
 * 默认是访问用户的空间，url类似user-1.html|user-2.html
 * 其他的是user-password.html|user-icon.html等等
 */
$action = input(1);

/**
 * 没有任何参数并且没有登录，直接去登录吧
 */
if ($action == 'password') {
    /**
     * 密码修改 测试通过
     */
    if (is_post || is_ajax) {
        //没有登录直接跳转
        $uid = session('uid') or error('你还没有登录', './visit-login.html');
        $old_password = md5(input('password_old'));
        $new_password = input('password_new');

        preg_match('#^\w{6,16}$#', $new_password) or error('新密码为长度6-16位数字字母下划线组成');
        //验证
        $user = db_find('select id,password from wlz_user where id="' . $uid . '"') or error('用户不存在');
        $user['password'] != $old_password and error('原密码不正确');

        //更新密码
        $sql = sprintf('update wlz_user set password="%s" where id="%s"', md5($new_password), $uid);
        db_query($sql) or error('修改密码失败');
        success('修改密码成功', 'user.html');

    } else {
        //没有登录直接去登录页面
        session('uid') or redirect('./visit-login.html');
        $title = '用户密码修改';
        include './view/user_password.html';
    }

} else if ($action == 'modify') {
    /**
     * 资料修改 测试通过
     */
    if (is_post || is_ajax) {

        $uid = session('uid') or error('你还没有登录', './visit-login.html');
        $data = array(
            'nickname' => input('nickname'),
            'phone' => input('phone'),
            'email' => input('email'),
            'info' => input('info'),
            'sex' => input('sex')
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

        //保存修改
        $sql = sprintf('update wlz_user set %s where id="%s"', db_str_concat($data), $uid);
        db_query($sql) or error('修改失败');
        success('修改成功', './user.html');

    } else {
        $uid = session('uid') or redirect('./visit-login.html');
        $title = '用户资料修改';
        $user = db_find('select nickname,phone,email,info,sex from wlz_user where id="' . $uid . '"') or error('用户不存在');
        include './view/user_modify.html';
    }


} else if ($action == 'logout') {
    /**
     * 安全退出  测试通过
     */
    session('uid', null);
    session('username', null);
    cookie('username', null);
    cookie('password', null);
    success('退出成功', 'visit-login.html');

} else if ($action == 'icon') {
    /**
     * 头像修改，测试通过
     */
    is_post or is_ajax or redirect('index.html');
    $uid = session('uid') or error('你还没有登录', 'visit-login.html');
    $user = db_find('select id from wlz_user where id="' . $uid . '"') or error('用户不存在');
    $result = upload_file(current($_FILES), path_upload_usericon,config('upload_image_ext'));
    //文件上传成功
    if ($result[0]) {
        $filename = path_upload_usericon . $uid . '.png';
        rename(path_upload_usericon . $result[1]['new_name'], $filename);
        images_zoom($filename, 50, 50);
        success('更换用户头像成功', '', array('src' => url_upload_usericon . $uid . '.png?' . mt_rand()));
    } else {
        error($result[1]);
    }

} else if ($action == 'theme') {
    /**
     * 用户的帖子 测试通过
     */
    //获取用户的id
    $uid = input(2) or $uid = session('uid') or redirect('visit-login.html');
    $user = db_find('select id,nickname from wlz_user where id="' . $uid . '"') or error('用户不存在');
    $page = input(3, 1);
    //页码显示
    $theme_num = db_field('select count(*) from wlz_theme where user_id="' . $uid . '"');
    $page_show = pages("user-theme-{$uid}-%s.html", $theme_num, $page, config('page_num'), $limit);

    //主题数据获取
    $sql = sprintf('select wlz_theme.id,wlz_theme.create_time,title,read_num,nickname,reply_num from wlz_theme inner join wlz_user on wlz_theme.user_id=wlz_user.id where wlz_theme.user_id="%s" order by create_time desc limit %s', $uid, $limit);
    $theme = db_select($sql);
    foreach ($theme as $k => $v) {
        $theme[$k]['create_time'] = format_time($v['create_time']);
    }

    $title = $user['nickname'] . '的帖子';
    include './view/user_theme.html';

} else if ($action == 'reply') {
    /**
     * 用户的回复
     */
    $uid = input(2) or $uid = session('uid') or redirect('visit-login.html');
    $user = db_find('select id,nickname from wlz_user where id="' . $uid . '"') or error('用户不存在');
    $page = input(3, 1);
    //页码显示
    $reply_num = db_field('select count(*) from wlz_reply where user_id="' . $uid . '"');
    $page_show = pages("user-reply-{$uid}-%s.html", $reply_num, $page, config('page_num'), $limit);

    //评论数据获取
    $sql = sprintf('select * from wlz_reply where user_id="%s" order by create_time desc limit %s', $uid, $limit);
    $reply = db_select($sql);
    foreach ($reply as $k => $v) {
        $reply[$k]['create_time'] = format_time($v['create_time']);
    }
    $title = $user['nickname'] . '的回复';
    include './view/user_reply.html';


} else {
    /**
     * 用户个人空间 测试通过
     */
    //获取用户id,既没有值也没有登录直接去登录页面
    $uid = $action or $uid = session('uid') or redirect('visit-login.html');

    //用户信息
    $user = db_find('select * from wlz_user where id="' . $uid . '"');
    empty($user) and error('用户不存在');
    $user['icon'] = format_user_icon($user['id']);
    $user['status'] = format_user_status($user['status']);
    $user['create_time'] = format_time($user['create_time']);
    $user['login_ip'] = long2ip($user['login_ip']);
    $user['login_time'] = format_time($user['login_time']);
    $user['sex'] = format_user_sex($user['sex']);

    $title = $user['nickname'] . '的空间';
    //自己看自己的空间
    $is_own = $uid == session('uid');
    $sql = sprintf("select id from wlz_friend where user_id='%s' and friend_id='%s'", session('uid'), $uid);
    $friend_action = db_find($sql) ? '删除好友' : '加为好友';

    include './view/user.html';
}
