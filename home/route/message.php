<?php

/**
 * 信息列表
 */
$uid = session('uid') or redirect('visit-login.html');
$page = input(1, 1);

//所有的消息设置为已读
db_query('update wlz_message set status=1 where user_id="'.$uid.'"');

//页码显示
$message_num = db_field('select count(*) from wlz_message where user_id="'.$uid.'"');

$page_show = pages("message-%s.html", $message_num, $page, config('page_num'), $limit);

//消息数据获取
$sql = sprintf('select * from wlz_message where user_id="%s" order by create_time desc limit %s', $uid, $limit);
$message = db_select($sql);
foreach ($message as $k => $v) {
    $message[$k]['create_time'] = format_time($v['create_time']);
}
$title = '消息列表';
include './view/message.html';



