<?php
/*
 * 版块控制器
 * 2016.4.21
 */
/**
 * section-1.html
 * section-1-1.html
 * section-1-1-1.html
 * 第一个参数为版块id
 * 第二个　：　排序规则
 * 第三个 ：页码
 */
$bid = input(1); //版块id
$order = input(2, 0); //排序规则
$page = input(3, 1); //页码

$section = db_find("select * from wlz_section where id='{$bid}'") or error('版块不存在');
if ($section['status'] == 1) {
    error('版块是关闭状态，暂时无法进入');
}

//最后评论时间(最新动态时间)  发布时间  点赞量   阅读量
$orders = array('reply_time', 'wlz_theme.create_time', 'praise_num', 'read_num');
$order_by = $orders[$order];

//页码显示
$page_show = pages("section-{$bid}-{$order}-%s.html", $section['theme_num'], $page, config('page_num'), $limit);

//主题信息
$sql = sprintf('select wlz_theme.id,wlz_theme.create_time,title,read_num,nickname,reply_num,top,wlz_theme.status from wlz_theme inner join wlz_user on wlz_theme.user_id=wlz_user.id where section_id="%s" order by top desc,%s desc limit %s', $bid, $order_by, $limit);
$theme = db_select($sql);
foreach ($theme as $k => $v) {
    $theme[$k]['create_time'] = format_time($v['create_time']);
}

//版主信息
$sql = sprintf('select wlz_user.id,nickname from  wlz_moderator inner join  wlz_user on  wlz_moderator.user_id= wlz_user.id where section_id="%s"', $bid);
$moder = db_select($sql);

$title = $section['name'];
include './view/section.html';


