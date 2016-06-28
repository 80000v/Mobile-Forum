<?php
$action = input(1);

if ($action == 'add') {
    /**
     * 发帖 测试通过
     */
    if (is_post || is_ajax) {
        $uid = session('uid') or error('你还没有登录', 'visit-login.html');
        strtoupper(input('code')) != session('code') and error('验证码不正确');
        $data = array(
            'title' => input('title'),
            'content' => input('content'),
            'section_id' => input('bid'),
            'user_id' => $uid,
            'create_time' => time(),
            'reply_time' => time()//为了排序而初始化的一个最后评论时间
        );
        $section = db_find('select id,status from wlz_section where id="' . $data['section_id'] . '"') or error('版块不存在');
        $section['status'] == 1 and error('版块被关闭，暂时无法发帖');
        mb_strlen($data['title'], 'UTF-8') < 5 and error('标题的长度不能少于5个字');
        mb_strlen($data['content'], 'UTF-8') < 15 and error('内容的长度不能少于15个字');

        //开启事务
        db_transaction();
        $result1 = db_query('update wlz_section set theme_num=theme_num+1 where id="' . $section['id'] . '"');
        $result2 = db_insert('wlz_theme', $data);
        if ($result1 && $result2) {
            $tid = db_insert_id();
            db_commit();
            //处理内容中的图片
            $images = get_str_images($data['content']);
            images_handler($images);
            //发贴成功，奖励积分
            credits_modify($uid, '+' . config('theme_add'));

            //发布成功破坏验证码
            session('code', mt_rand());
            success('发表帖子成功', 'theme-' . $tid . '.html');
        } else {
            db_rollback();
            error('发布帖子失败');
        }

    } else {
        $uid = session('uid') or redirect('visit-login.html');
        $bid = input(2);
        $section = db_find('select id,name from  wlz_section where id="' . $bid . '"') or error('版块不存在');
        $title = '发表帖子';
        include './view/theme_add.html';
    }


} else if ($action == 'modify') {
    /**
     * 修改帖子
     */
    $uid = session('uid') or error('你还没有登录', 'visit-login.html');
    if (is_post || is_ajax) {
        $tid = intval(input('tid'));
        $theme = db_find('select id,content,user_id from wlz_theme where id="' . $tid . '"') or error('你要修改的帖子不存在');
        $theme['user_id'] == $uid or error('你没有权限修改帖子');
        $data = array(
            'title' => input('title'),
            'content' => input('content'),
            'modify_time' => time()//修改时间
        );
        mb_strlen($data['title'], 'UTF-8') < 5 and error('标题的长度不能少于5个字');
        mb_strlen($data['content'], 'UTF-8') < 15 and error('内容的长度不能少于15个字');

        $sql = sprintf('update wlz_theme set %s where id="%s"', db_str_concat($data), $tid);
        if (db_query($sql)) {
            //修改成功之后，处理images
            $old_images = get_str_images($theme['content']);
            $new_images = get_str_images($data['content']);
            images_handler($new_images, $old_images);
            success('修改成功', 'theme-' . $tid . '.html');
        } else {
            error('修改失败');
        }

    } else {
        $tid = input(2);
        $theme = db_find('select * from wlz_theme where id="' . $tid . '"') or error('帖子不存在');
        $theme['user_id'] != $uid and error('你没有权限修改帖子');
        $section = db_find('select * from wlz_section where id="' . $theme['section_id'] . '"') or error('版块不存在');
        $title = '修改帖子';
        include './view/theme_modify.html';
    }


} else if ($action == 'delete') {
    /**
     * 删除帖子 测试通过
     */
    is_post or is_ajax or error('页面君起飞了');

    //获取用户的id,没有登录直接去登录
    $uid = session('uid') or error('你还没有登录', 'visit-login.html');
    $user = db_find('select * from wlz_user where id="' . $uid . '"') or error('用户不存在');
    $tid = input(2);

    $theme = db_find('select id,user_id,section_id,title,content from wlz_theme where id="' . $tid . '"') or error('帖子不存在');

    //不是帖子的主人并且不是版主
    if ($uid != $theme['user_id'] && !is_moder($uid, $theme['section_id'])) {
        error('你没有权限删除此帖子');
    }
    db_transaction();
    //删除评论中的图片
    $reply = db_select('select content from wlz_reply where theme_id="' . $tid . '"');
    foreach ($reply as $v) {
        $images = get_str_images($v['content']);
        images_handler(array(), $images);
    }
    //删除所有评论
    $result1 = db_query('delete from wlz_reply where theme_id="' . $tid . '"');
    //删除所有收藏
    $result2 = db_query('delete from wlz_collection where theme_id="' . $tid . '"');
    //删除帖子本身
    $result3 = db_query('delete from wlz_theme where id="' . $tid . '"');
    //更新版块帖子数量
    $result4 = db_query('update wlz_section set theme_num=theme_num-1 where id="' . $theme['section_id'] . '"');

    if ($result1 && $result2 && $result3 && $result4) {
        db_commit();
        //删除内容中的图片
        $images = get_str_images($theme['content']);
        images_handler(array(), $images);

        //不是删除自己的帖子，那一定是版主在删除别人的帖子，需要发送内信给用户
        $message = sprintf('你的帖子【%s】被版主 <a href="user-%s.html">%s</a> 删除了,', $theme['title'], $uid, $user['nickname']);
        $uid != $theme['user_id'] and send_message($theme['user_id'], $message);
        success('删除成功', 'section-' . $theme['section_id'] . '.html');
    } else {
        db_rollback();
        error('删除帖子失败');
    }


} else if ($action == 'praise') {
    /**
     * 点赞 测试通过
     */
    is_post or is_ajax or error("页面没有找到");
    $tid = input(2);

    //如果存在cookie的记录就是已经点过赞了
    cookie('praise' . $tid) and error('你已经点过赞了');

    if (db_query('update wlz_theme set praise_num=praise_num+1 where id="' . $tid . '"')) {
        cookie('praise' . $tid, 1, 3600 * 24);
        $num = db_field('select praise_num from wlz_theme where id="' . $tid . '"');
        success('点赞成功', '', array('num' => intval($num)));
    } else {
        error('点赞失败');
    }

} else if ($action == 'lock') {
    /**
     * 帖子锁定
     */
    is_post or is_ajax or error("页面没有找到");
    $uid = session('uid') or error('你还没有登录', 'visit-login.html');
    $tid = input(2);
    $user = db_find('select id,status from wlz_user where id="' . $uid . '"') or error('用户不存在');
    $theme = db_find('select status,section_id,user_id from wlz_theme where id="' . $tid . '"') or error('帖子不存在');

    //权限检测
    if ($uid != $theme['user_id'] && !is_moder($uid, $theme['section_id'])) {
        error('你没有权限管理帖子');
    }

    if ($theme['status'] == 1) {
        db_query('update wlz_theme set status=0 where id="' . $tid . '"') or error('解锁失败');
        success('解锁成功', '', array('text' => '锁帖'));
    } else {
        db_query('update wlz_theme set status=1 where id="' . $tid . '"') or error('锁定失败');
        success('锁定成功', '', array('text' => '解锁'));
    }

} else if ($action == 'top') {
    is_post or is_ajax or error("页面没有找到");
    $uid = session('uid') or error('你还没有登录', 'visit-login.html');
    $user = db_find('select id,status from wlz_user where id="' . $uid . '"') or error('用户不存在');
    $tid = input(2);
    $theme = db_find('select id,section_id,top from wlz_theme where id="' . $tid . '"') or error('帖子不存在');
    is_moder($uid, $theme['section_id']) or error('你没有权限');
    if ($theme['top'] == 0) {
        db_query('update wlz_theme set top=1 where id="' . $tid . '"') or error('置顶失败');
        success('置顶成功', '', array('text' => '去顶'));
    } else {
        db_query('update wlz_theme set top=0 where id="' . $tid . '"') or error('去顶失败');
        success('去顶成功', '', array('text' => '置顶'));
    }

} else if ($action == 'reply') {
    /**
     * 帖子评论 测试通过
     */
    is_post or is_ajax or error('页面没有找到');
    //获取参数
    $uid = session('uid') or error('你还没有登录', 'visit-login.html');
    $reply_uid = intval(input('reply_uid'));
    $data = array(
        'content' => input('content'),
        'user_id' => $uid,
        'theme_id' => intval(input(2)),
        'create_time' => time()
    );

    //必要性判断
    (mb_strlen($data['content'], 'UTF-8') < 5) and error('评论内容不能少于5个字');
    $theme = db_find('select id,status,user_id,title from wlz_theme where id="' . $data['theme_id'] . '"') or error('你要评论的帖子被删除了');
    $theme['status'] == 1 and error('帖子被锁定，不能进行评论');

    //执行添加
    if (db_insert('wlz_reply', $data)) {
        //获取内容中的图片
        $images = get_str_images($data['content']);
        //转移并记录图片
        images_handler($images);

        $cmt_id = db_insert_id();
        //奖励评论积分
        credits_modify($uid, '+' . config('reply_add'));
        //如果回帖的不是帖主，发送消息给帖主
        $message = sprintf('你的帖子【<a href="theme-%s.html">%s</a>】被回复了,快去查看吧！', $theme['id'], $theme['title']);
        $uid != $theme['user_id'] and send_message($theme['user_id'], $message);
        //如果是回复楼层的，给楼层的用户发送提示消息
        $message = sprintf('有人在帖子【<a href="theme-%s.html">%s</a>】中回复你了，快去查看吧！', $theme['id'], $theme['title']);
        $uid != $reply_uid and send_message($reply_uid, $message);
        //更新评论量和最后评论时间
        db_query('update wlz_theme set reply_num=reply_num+1,reply_time=' . $data['create_time'] . ' where id="' . $theme['id'] . '"');
        //返回评论内容
        $user = db_find('select * from wlz_user where id="' . $uid . '"');
        $reply_num = db_field('select reply_num from wlz_theme where id="' . $theme['id'] . '"');
        success('评论成功', '', array(
            'cmt' => format_ubb($data['content']),
            'cmt_time' => format_time($data['create_time']),
            'cmt_id' => $cmt_id,
            'cmt_num' => $reply_num + 1,
            'user_icon' => format_user_icon($user['id']),
            'user_nickname' => $user['nickname'],
            'user_id' => $user['id']
        ));
    } else {
        error('评论失败');
    }

} else if ($action == 'images') {
    /**
     * 上传图片
     */
    is_post or is_ajax or redirect('index.html');
    $uid = session('uid') or error('你还没有登录', 'visit-login.html');
    $tid = input('tid');

    //上传图片
    $result = upload_file(current($_FILES), path_upload_temp,config('upload_image_ext'));
    if ($result[0]) {
        success('上传图片成功', '', array('text' => '[img=' . url_upload_images . $result[1]['new_name'] . ']'));
    } else {
        error($result[1]);
    }

} else {
    /**
     * 查看帖子 测试通过
     */
    //参数
    $tid = input(1);
    $uid = session('uid');

    //主题
    $theme = db_find('select * from wlz_theme where id="' . $tid . '"') or error('帖子不存在');
    $theme['create_time'] = format_time($theme['create_time']);
    $theme['reply_time'] = format_time($theme['reply_time']);
    $theme['modify'] = $theme['modify_time'] ? 1 : 0;//只要修改了就是1
    $theme['modify_time'] = format_time($theme['modify_time']);
    $theme['content'] = format_ubb($theme['content']);

    //版块
    $section = db_find('select * from wlz_section where id="' . $theme['section_id'] . '"');
    $section['status'] == 0 or error('版块关闭，无法查看帖子');
    //作者(作者在逻辑上不能被删除，否则存在大量幽灵文件和数据)
    $author = db_find('select * from wlz_user where id="' . $theme['user_id'] . '"') or error('作者信息不存在');
    $author['icon'] = format_user_icon($author['id']);

    //模板辅助变量
    $like = cookie('praise' . $tid) ? 'icon-likefill' : 'icon-like';
    $lock = $theme['status'] ? '解锁' : '锁帖';
    $top = $theme['top'] ? '去顶' : '置顶';
    $sql = sprintf('select count(*) from wlz_collection where theme_id="%s" and user_id="%s"', $tid, $uid);
    $favor = db_field($sql) ? 'icon-favorfill' : 'icon-favor';
    $is_master = $theme['user_id'] == $uid;
    $sql = sprintf('select count(*) from wlz_moderator where section_id=%s and user_id="%s"', $section['id'], $uid);
    $is_moder = db_field($sql);

    //评论信息
    $sql = sprintf('select wlz_reply.id as rid,wlz_user.id as uid,nickname,content,wlz_reply.create_time from wlz_reply inner join wlz_user on wlz_reply.user_id=wlz_user.id where theme_id="%s" order by wlz_reply.create_time', $tid);
    $reply = db_select($sql);
    $total = 1;
    foreach ($reply as $k => $v) {
        $reply[$k]['cnt'] = $total++;
        $reply[$k]['icon'] = format_user_icon($v['uid']);
        $reply[$k]['create_time'] = format_time($v['create_time']);
        $reply[$k]['content'] = format_ubb($v['content']);
    }

    //看完帖子，帖子阅读量加1
    db_query('update wlz_theme set read_num=read_num+1 where id="' . $tid . '"');

    $title = $theme['title'];
    include './view/theme.html';
}


