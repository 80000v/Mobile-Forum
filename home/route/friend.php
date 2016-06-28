<?php
$action = input(1);

if ($action == 'toggle') {
    /**
     * 添加好友
     */
    is_post or is_ajax or error('页面没有找到');
    $uid = session('uid') or error('请先登录', 'visit-login.html');
    $fid = input(2);
    if ($uid == $fid) error('不要添加自己为好友');

    $sql = sprintf('select id from wlz_friend where user_id="%s" and friend_id="%s"', $uid, $fid);
    //已经添加为好友
    if (db_find($sql)) {
        $sql = sprintf('delete from wlz_friend where user_id="%s" and friend_id="%s"', $uid, $fid);
        db_query($sql) === false ? error('删除好友失败') : success('删除好友成功', '', array('text' => '加为好友'));

    } else {
        db_insert('wlz_friend', array(
            'user_id' => $uid,
            'friend_id' => $fid
        )) === false ? error('添加好友失败') : success('添加好友成功', '', array('text' => '删除好友'));
    }

} else {
    /**
     * 用户好友列表
     */
    $uid = session('uid') or redirect('visit-login.html');
    $user = db_find('select id,nickname from wlz_user where id="' . $uid . '"') or error('用户不存在');
    $page = input(1, 1);
    //页码显示
    $friend_num = db_field('select count(*) from wlz_friend where user_id="' . $uid . '"');
    $page_show = pages("friend-%s.html", $friend_num, $page, config('page_num'), $limit);

    //好友数据获取
    $sql = sprintf('select wlz_user.id as id,nickname from wlz_friend inner join wlz_user on wlz_friend.friend_id=wlz_user.id where wlz_friend.user_id="%s" order by nickname desc limit %s', $uid, $limit);
    $friend = db_select($sql);
    foreach ($friend as $k => $v) {
        $friend[$k]['icon'] = format_user_icon($v['id']);
    }
    $title = '好友列表';
    include './view/friend.html';

}