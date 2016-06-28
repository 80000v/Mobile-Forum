<?php
/*
 * 版主管理
 */
defined('shouing') or die('Access is denied.');
$action = input(1);

if ($action == 'add') {
    /**
     * 添加版主
     */
    if (is_ajax || is_post) {

        //用户名
        $username = input('username');
        $sid = intval(input('sid'));
        $user = db_find('select id from wlz_user where username="' . $username . '"') or error('用户不存在');
        $section = db_find('select * from wlz_section where id="' . $sid . '"') or error('版块不存在');
        $sql = sprintf('select * from wlz_moderator where user_id="%s" and section_id="%s"', $user['id'], $sid);
        db_find($sql) and error('此用户已经是此版块的版主');

        $data = array(
            'section_id' => $sid,
            'user_id' => $user['id'],
            'create_time' => time()
        );
        if (db_insert('wlz_moderator', $data)) {
            success('添加版主成功', 'moderator.html');
        } else {
            error('添加版主失败');
        }

    } else {
        $sections = db_select('select id,name from wlz_section');
        $title = '添加版主';
        include './view/moderator_add.html';
    }


} else if ($action == 'del') {
    /**
     * 删除版zhu
     */
    if (is_ajax) {
        $mid = input(2);//版主序列id
        db_data_exists('wlz_moderator', $mid) or error('要删除的版主信息不存在');
        if (db_query('delete from wlz_moderator where id="' . $mid . '"')) {
            success('删除版主成功');
        } else {
            error('删除版主失败');
        }
    }

} else {
    /**
     * 版主管理
     */
    $sql = 'select wlz_moderator.id as id,wlz_section.id as sid,wlz_section.name,wlz_user.username,wlz_user.nickname,wlz_user.id as uid from wlz_section left join wlz_moderator on wlz_section.id=wlz_moderator.section_id inner join wlz_user on wlz_moderator.user_id=wlz_user.id';
    $sections = db_select($sql);
    $title = '版主管理';
    include './view/moderator.html';
}