<?php

include '../init.php';

function sss($str)
{
    return extension_loaded($str) ? '开启' : '关闭';
}

echo '<br />下面的模块必须开启才能正常使用本程序！<br />';
echo '<br />gd状态:'.sss('gd');
echo '<br />mysqli状态:'.sss('mysqli');
echo '<br />openssl状态:'.sss('openssl');
echo '<br />sockets状态:'.sss('sockets');
echo '<br />exif状态:'.sss('exif');
echo '<br />mbstring状态:'.sss('mbstring');

echo '<br /><br /><br />安装前请先到config/base.config.php文件中配置好！<br /><br />';


db_query('drop table if exists wlz_admin');
db_query('drop table if exists wlz_images');
db_query('drop table if exists wlz_download');
db_query('drop table if exists wlz_attachment');
db_query('drop table if exists wlz_message');
db_query('drop table if exists wlz_moderator');
db_query('drop table if exists wlz_collection');
db_query('drop table if exists wlz_session');
db_query('drop table if exists wlz_friend');
db_query('drop table if exists wlz_reply');
db_query('drop table if exists wlz_theme');
db_query('drop table if exists wlz_section');
db_query('drop table if exists wlz_user');


$sql = "create table wlz_session(
id char(40) not null default '' primary key comment 'session id',
data varchar(20000) not null default '' comment 'session数据',
atime int unsigned not null default 0 comment '最新时间',
ip int unsigned not null default 0 comment '用户ip'
)engine myisam charset utf8";


if (db_query($sql)) {
    echo 'create table wlz_session success!';
} else {
    echo 'create table wlz_session fail!';
}
echo '<br />';

$sql = "create table wlz_user(
id int unsigned primary key auto_increment,
username char(30) not null default '' comment '登录账号',
password char(32) not null default '' comment '密码',
phone char(11) not null default '' comment '手机号',
email char(50) not null default '' comment '邮箱',
nickname char(30) not null default '' comment '网站昵称',
sex int not null default 0 comment '性别',
credits int unsigned not null default 0 comment '积分',
info varchar(500) not null default '' comment '个性签名',
create_time int unsigned not null default 0 comment '注册时间',
login_time int unsigned not null default 0 comment '最后登录时间',
login_ip int unsigned not null default 0 comment '最后登录ip',
status int unsigned not null default 0 comment '状态0正常1锁定2未审核'
)engine innodb charset utf8";

if (db_query($sql)) {
    echo 'create table wlz_user success!';
} else {
    echo 'create table wlz_user fail!';
}
echo '<br />';
$sql = "create table wlz_friend(
id int unsigned primary key auto_increment,
user_id int unsigned not null default 0 comment '用户',
friend_id int unsigned not null default 0 comment '好友',
remarks char(60) not null default '' comment '备注',
constraint friend_user_id_fk foreign key (user_id) REFERENCES wlz_user(id),
CONSTRAINT friend_friend_id_fk FOREIGN key (friend_id) REFERENCES wlz_user(id)
)engine innodb charset utf8";

if (db_query($sql)) {
    echo 'create table wlz_friend success!';
} else {
    echo 'create table wlz_friend fail!';
}
echo '<br />';
$sql = "create table wlz_section(
    id int unsigned primary key auto_increment,
name varchar(400) not null DEFAULT '' comment '版块名',
info varchar(1000) not null DEFAULT '' comment '版块简介',
order_value int unsigned not null default 0 comment '版块排序值',
theme_num int unsigned not null default 0 comment '帖子数量',
today_theme_num int unsigned not null default 0 comment '今天帖子数量',
status int not null default 0 comment '是否关闭论坛'
)engine innodb charset utf8";

if (db_query($sql)) {
    echo 'create table wlz_section success!';
} else {
    echo 'create table wlz_section fail!';
}
echo '<br />';
$sql = "create table wlz_theme(
    id int unsigned primary key auto_increment,
title varchar(200) not null default '' comment '标题',
content varchar(20000) not null default '' comment '内容',
read_num int unsigned not null default 0 comment '阅读数量',
praise_num int unsigned not null default 0 comment '点赞数量',
reply_num int unsigned not null default 0 comment '评论数量',
modify_time int unsigned not null default 0 comment '最后修改时间',
reply_time int unsigned not null default 0 comment '最后评论时间',
create_time int unsigned not null default 0 comment '创建时间',
top tinyint not null default 0 comment '置顶',
status tinyint not null default 0 comment '锁定',
user_id int unsigned not null default 0 comment '所属用户',
section_id int unsigned not null default 0 comment '所属版块',
CONSTRAINT theme_user_id_fk FOREIGN key(user_id) REFERENCES wlz_user(id),
CONSTRAINT theme_section_id_fk FOREIGN key(section_id) REFERENCES wlz_section(id)
)engine innodb charset utf8";

if (db_query($sql)) {
    echo 'create table wlz_theme success!';
} else {
    echo 'create table wlz_theme fail!';
}
echo '<br />';
$sql = "create table wlz_reply(
    id int unsigned primary key auto_increment,
content varchar(20000) not null default '' comment '内容',
create_time int unsigned not null default 0 comment '发布时间',
user_id int unsigned not null default 0 comment '回复的用户',
theme_id int unsigned not null default 0 comment '回复的主题',
CONSTRAINT reply_user_id_fk FOREIGN key(user_id) REFERENCES wlz_user(id),
CONSTRAINT reply_theme_id_fk FOREIGN key(theme_id) REFERENCES wlz_theme(id)
)engine innodb charset utf8";

if (db_query($sql)) {
    echo 'create table wlz_reply success!';
} else {
    echo 'create table wlz_reply fail!';
}
echo '<br />';
$sql = "create table wlz_collection(
    id int unsigned primary key auto_increment,
create_time int unsigned not null default 0 comment '收藏时间',
user_id int unsigned not null default 0 comment '所属用户',
theme_id int unsigned not null default 0 comment '目标帖子',
CONSTRAINT collection_user_id_fk FOREIGN key(user_id) REFERENCES wlz_user(id),
CONSTRAINT collection_theme_id_fk FOREIGN key(theme_id) REFERENCES wlz_theme(id)
)engine innodb charset utf8";

if (db_query($sql)) {
    echo 'create table wlz_collection success!';
} else {
    echo 'create table wlz_collection fail!';
}
echo '<br />';

$sql = "create table wlz_moderator(
    id int unsigned primary key auto_increment,
create_time int unsigned not null default 0 comment '设置版主时间',
user_id int unsigned not null default 0 comment '用户',
section_id int unsigned not null default 0 comment '版块',
CONSTRAINT moderator_user_id_fk FOREIGN key(user_id) REFERENCES wlz_user(id),
CONSTRAINT moderator_theme_id_fk FOREIGN key(section_id) REFERENCES wlz_section(id)
)engine innodb charset utf8";

if (db_query($sql)) {
    echo 'create table wlz_moderator success!';
} else {
    echo 'create table wlz_moderator fail!';
}
echo '<br />';
$sql = "create table wlz_message(
    id int unsigned primary key auto_increment,
content varchar(2000) not null default '' comment '内容',
create_time int unsigned not null default 0 comment '接收时间',
status tinyint not null default 0 comment '是否查看',
user_id int unsigned not null default 0 comment '接收用户',
CONSTRAINT message_user_id_fk FOREIGN key(user_id) REFERENCES wlz_user(id)
)engine innodb charset utf8";

if (db_query($sql)) {
    echo 'create table wlz_message success!';
} else {
    echo 'create table wlz_message fail!';
}
echo '<br />';

$sql = "create table wlz_attachment(
    id int unsigned primary key auto_increment,
size int unsigned not null default 0 comment '附件大小',
new_name char(200) not null default '' comment '上传时生产的新名称',
old_name char(100) not null default '' comment '上传之前的名称',
price int unsigned not null default 0 comment '下载所需要的积分',
create_time int unsigned not null default 0 comment '上传时间',
user_id int unsigned not null default 0 comment '附件所属用户',
CONSTRAINT attachment_user_id_fk FOREIGN key(user_id) REFERENCES wlz_user(id)
)engine innodb charset utf8";

if (db_query($sql)) {
    echo 'create table wlz_attachment success!';
} else {
    echo 'create table wlz_attachment fail!';
}
echo '<br />';
$sql = "create table wlz_download(
    attachment_id int unsigned not null default 0 comment '附件id',
user_id int unsigned not null default 0 comment '下载的用户',
create_time int unsigned not null default 0 comment '下载时间',
CONSTRAINT download_user_id_fk FOREIGN key(user_id) REFERENCES wlz_user(id)
)engine innodb charset utf8";

if (db_query($sql)) {
    echo 'create table wlz_download success!';
} else {
    echo 'create table wlz_download fail!';
}
echo '<br />';
$sql = "create table wlz_images(
    name varchar(50) not null default '' ,
num int not null default 0
)engine innodb charset utf8";

if (db_query($sql)) {
    echo 'create table wlz_images success!';
} else {
    echo 'create table wlz_images fail!';
}
echo '<br />';
$sql = "create table wlz_admin(
    id int unsigned primary key auto_increment,
username char(24) not null default '',
password char(32) not null default '',
login_time int unsigned not null default 0,
login_ip int unsigned not null default 0
)engine innodb charset utf8";

if (db_query($sql)) {
    echo 'create table wlz_admin success!';
} else {
    echo 'create table wlz_admin fail!';
}
echo '<br />';
$sql = "insert into wlz_admin(username, password) values('admin', '21232f297a57a5a743894a0e4a801fc3')";
if (db_query($sql)) {
    echo 'add admin success!';
} else {
    echo 'add admin fail!';
}
echo '<br />';

echo '以上全是success表示创建成功，出现了fail就按安装失败,<br />安装完一定删除此文件';