<?php
/*
 * 基础配置文件
 */
return array(

    //数据库配置
    'db_host' => 'xx',
    'db_user' => 'xx',
    'db_pwd' => 'xx',
    'db_name' => 'xx',
    //时区
    'time_zone' => 'Prc',

    //session有效期
    'session_lifetime' => 3600,

    //文件上传配置
    'upload_size' => 20000000,//单位是b
    'upload_image_ext' => 'jpg|png|gif',//允许上传的图片类型
    'upload_attachment_ext' => 'rar|zip|jpg|png|gif',//附件类型

    //一页显示多少个帖子
    'page_num' => 10,

    //发帖回帖奖励金币设置
    'theme_add' => 5,//发帖奖励金币
    'reply_add' => 1,//回帖奖励金币

    //网站的信息配置
    'site_name' => '无节操',
    'site_url' => 'wujiecao.com',
    'site_keywords' => '手机端|轻量级|论坛程序',
    'site_description' => '专注手机端轻量级论坛程序',


    //smtp邮箱服务器配置
    'email' => '799034851@qq.com',
    'email_name' => '799034851',
    'email_pwd' => 'tbyduuggtxrybedj',

);