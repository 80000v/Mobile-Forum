<?php
/*
 * 版块相关
 *
 * 2016.4.21
 *
 */
defined('shouing') or die('Access is denied.');
$action = input(1);

if ($action == 'add') {
    /**
     * 版块添加
     */
    if (is_post || is_ajax) {

        $data = array(
            'name' => input('name'),
            'info' => input('info'),
            'status' => intval(input('status')),
            'order_value' => intval(input('order_value'))
        );
        db_find('select id from wlz_section where name="' . $data['name'] . '"') and error('版块名称已经存在');
        empty($data['name']) and error('版块名不能为空');
        in_array($data['status'], array(0, 1)) or error('版块的状态设置有误');

        if (db_insert('wlz_section', $data)) {
            success('添加版块成功', 'section.html');
        } else {
            error('添加版块失败');
        }

    } else {
        $title = '添加版块';
        include './view/section_add.html';
    }
} else if ($action == 'delete') {
    /**
     * 版块删除
     */
    if (is_ajax) {
        $sid = input(2);
        $section = db_find('select * from wlz_section where id="' . $sid . '"') or error('你要删除的版块不存在');
        //拼接成版块的图标物理位置，用于后面的删除
        $icon = path_upload_sectionicon . $section['id'] . '.png';

        if (db_select('select id from wlz_theme where section_id="' . $sid . '"')) {
            error('版块不为空，不能直接删除');
        }

        //删除版块
        if (db_query('delete from wlz_section where id="' . $sid . '"')) {
            //删除版块的图标
            is_file($icon) and unlink($icon);
            success('删除版块成功');
        } else {
            error('删除版块失败');
        }
    }

} else if ($action == 'modify') {
    /**
     * 版块修改
     */
    if (is_ajax) {
        $sid = input(2);
        $section = db_find('select id,name from wlz_section where id="' . $sid . '"') or error('你要修改的版块不存在');
        $data = array(
            'name' => input('name'),
            'info' => input('info'),
            'status' => intval(input('status')),
            'order_value' => intval(input('order_value'))
        );
        if ($section['name'] == $data['name'] && $section['id'] != $sid) {
            error('版块名已经被使用');
        }
        empty($data['name']) and error('版块名不能为空');


        in_array($data['status'], array(0, 1)) or error('版块的状态设置有误');
        $sql = sprintf('update wlz_section set %s where id="%s"', db_str_concat($data, '='), $sid);
        if (db_query($sql)) {
            success('修改版块成功', 'section.html');
        } else {
            error('修改版块失败');
        }

    } else {
        $bid = input(2);
        $section = db_find('select * from wlz_section where id="' . $bid . '"') or error('版块不存在');
        $title = '版块修改';
        include './view/section_modify.html';
    }
} else if ($action == 'icon') {

    if (is_post || is_ajax) {
        $bid = intval(input(2));
        db_find('select id from wlz_section where id="' . $bid . '"') or error('你要修改图标的版块不存在');

        //上传图标
        $result = upload_file(current($_FILES), path_upload_temp, config('upload_image_ext'));
        //如果上传成功
        if ($result[0]) {
            $new_name = path_upload_sectionicon . $bid . '.png';
            rename(path_upload_temp . $result[1]['new_name'], $new_name);
            images_zoom($new_name, 50, 50);
            success('更换版块图标成功', '', array('src' => url_upload_sectionicon . $bid . '.png?' . mt_rand()));
        } else {
            error($result[1]);
        }
    }

} else {

    $sections = db_select('select * from wlz_section order by order_value');
    //调整数据
    foreach ($sections as $key => $section) {
        $sections[$key]['icon'] = format_section_icon($section['id']);
        $sections[$key]['status'] = format_section_status($section['status']);
    }
    $title = '版块管理';
    include './view/section.html';
}