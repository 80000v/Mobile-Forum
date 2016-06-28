<?php

//tabs 
$theme_dynamic = db_select('select id,title,reply_time from wlz_theme order by reply_time desc limit 10');
foreach ($theme_dynamic as $key => $value) {
	$theme_dynamic[$key]['reply_time'] = format_time($value['reply_time']);
}

$theme_new = db_select('select id,title,create_time from wlz_theme order by create_time desc limit 10');
foreach ($theme_new as $key => $value) {
	$theme_new[$key]['create_time'] = format_time($value['create_time']);
}

$theme_praise = db_select('select id,title,praise_num from wlz_theme order by praise_num desc limit 10');

$theme_read = db_select('select id,title,read_num from wlz_theme order by read_num desc limit 10');

//板块信息
$forums = db_select('select * from wlz_section order by order_value asc');
foreach ($forums as $key => $forum) {
    $forums[$key]['status'] = format_section_status($forum['status']);
    $forums[$key]['icon'] = format_section_icon($forum['id']);
}
$title = config('site_name').' - '.config('site_url');
include './view/index.html';