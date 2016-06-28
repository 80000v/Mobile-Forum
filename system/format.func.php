<?php
/*
 * 数据格式化函数
 * 2016.4.21
 */

/**
 * @param $section
 * 版块信息格式化函数，用于页面显示
 */
function section_format(&$section)
{
    //版块图标
    if (isset($section['icon'])) {
        if ($section['icon'] == '') {
            $section['icon'] = url_images . 'default_section_icon.jpg';
        } else {
            $section['icon'] = url_upload_sectionicon . $section['icon'];
        }
    }

    //版块状态
    $status = array('正常', '关闭');
    isset($section['close']) and $section['close'] = $status[$section['close']];
}


/**
 * @param $user_id
 * @return string
 * 用户头像格式化函数
 */
function format_user_icon($user_id)
{
    if (is_file(path_upload_usericon . $user_id . '.png')) {
        return url_upload_usericon . $user_id . '.png';
    } else {
        return url_images . 'default_user_icon.jpg';
    }
}

/**
 * @param $section_id
 * @return string
 * 版块图标格式化
 */
function format_section_icon($section_id)
{
    if (is_file(path_upload_sectionicon . $section_id . '.png')) {
        return url_upload_sectionicon . $section_id . '.png';
    } else {
        return url_images . 'default_section_icon.jpg';
    }
}

/**
 * @param $status
 * @return mixed
 * 格式化版块状态
 */
function format_section_status($status)
{
    $arr = array('正常', '关闭');
    return $arr[$status];
}

/**
 * @param $time
 * @return string
 * 格式化时间
 */
function format_time($time)
{
    $diff = time() - $time;

    if ($diff < 60) {
        $time = $diff . '秒前';
    } else if ($diff < 3600) {
        $time = floor($diff / 60) . '分钟前';
    } else if ($diff < 86400) {
        $time = floor($diff / 3600) . '小时前';
    } else {
        $time = floor($diff / 86400) . '天前';
    }
    return $time;
}

/**
 * @param $size
 * @return string
 * 大小格式化
 */
function format_size($size)
{
    $array = array('B', 'KB', 'MB', 'GB', 'TB');
    $i = 0;
    while ($size / 1024 > 1) {
        $size /= 1024;
        $i++;
    }
    return number_format($size, 2) . $array[$i];
}

/**
 * @param $sex
 * @return mixed
 * 用户性别格式化
 */
function format_user_sex($sex)
{
    $arr = array('未知', '男', '女');
    return $arr[$sex];
}

/**
 * @param $status
 * @return mixed
 * 用户状态格式化
 */
function format_user_status($status)
{
    $arr = array('正常', '锁定', '未审核');
    return $arr[$status];
}

function format_ubb($str)
{
    $pattern = array(
        '#\[img=(.*?)\]#s',//图片
        '#\[attachment=(.*?)\](.*?)\[\/attachment\]#s',//附件
        '#\[br\]#s',
        '#\[b\](.*?)\[\/b\]#s',
        '#\[a=(.*?)\](.*?)\[\/a\]#s',
        '#\[color=(.*?)\](.*?)\[\/color\]#s'
    );
    $replacement = array(
        '<img src="${1}"/>',
        '<a href="attachment-${1}.html"><i class="icon icon-attachment"></i>${2}</a>',
        '<br />',
        '<b>${1}</b>',
        '<a href="${1}">${2}</a>',
        '<span style="color:${1}">${2}</span>'
    );

    return preg_replace($pattern, $replacement, $str);
}