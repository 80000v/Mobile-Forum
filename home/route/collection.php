<?php
$action = input(1);

if ($action == 'toggle') {
    /**
     * 收藏帖子  测试通过
     */
    is_post or is_ajax or error('页面没有找到');
    $uid = session('uid') or error('请先登录', 'visit-login.html');
    $tid = input(2);

    $sql = sprintf('select id from wlz_collection where user_id="%s" and theme_id="%s"', $uid, $tid);
    //已经收藏
    if (db_find($sql)) {
        $sql = sprintf('delete from wlz_collection where user_id="%s" and theme_id="%s"', $uid, $tid);
        db_query($sql) === false ? error('取消收藏失败') : success('已取消收藏');
    } else {
        db_insert('wlz_collection', array(
            'user_id' => $uid,
            'theme_id' => $tid,
            'create_time' => time()
        )) === false ? error('收藏失败') : success('收藏成功');
    }

} else {

    /**
     * 用户收藏列表
     */
    $uid = $action or $uid = session('uid') or redirect('visit-login.html');
    $user = db_find('select id,nickname from wlz_user where id="' . $uid . '"') or error('用户不存在');
    $page = input(2, 1);
    //页码显示
    $collection_num = db_field('select count(*) from wlz_collection where user_id="' . $uid . '"');
    $page_show = pages("collection-{$uid}-%s.html", $collection_num, $page, config('page_num'), $limit);

    //收藏数据获取
    $sql = sprintf('select wlz_theme.id as id,wlz_collection.create_time as time,title from wlz_collection inner join wlz_theme on wlz_collection.theme_id=wlz_theme.id where wlz_collection.user_id="%s" order by wlz_collection.create_time desc limit %s', $uid, $limit);
    $collection = db_select($sql);
    foreach ($collection as $k => $v) {
        $collection[$k]['time'] = format_time($v['time']);
    }
    $title = $user['nickname'] . '的收藏';
    include './view/collection.html';

}