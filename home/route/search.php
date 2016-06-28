<?php

$key = input('key') or $key = input(1);//关键字
$page = input(2, 1); //页码



$result_num = db_field('select count(*) from wlz_theme where title like "%'.$key.'%"');

$page_show = pages("search-{$key}-%s.html", $result_num, $page, config('page_num'), $limit);
//主题信息
$sql = 'select wlz_theme.id,wlz_theme.create_time,title,read_num,nickname,reply_num from wlz_theme inner join wlz_user on wlz_theme.user_id=wlz_user.id where title like "%'.$key.'%" order by create_time desc limit '.$limit;
$theme = db_select($sql);
foreach ($theme as $k => $v) {
    $theme[$k]['create_time'] = format_time($v['create_time']);
}

$title = $key;
include './view/search.html';