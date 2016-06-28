<?php
defined('shouing') or die('Access is denied.');
/*
 *
 * 附件管理
 */

$action = input(1);

if ($action == 'del') {
    /**
     * 附件删除
     */
    $fid = input(2);//附件id
    $attachment = db_find('select * from wlz_attachment where id="' . $fid . '"') or error('你要删除的附件不存在');
    if (db_query('delete from wlz_attachment where id="' . $fid . '"')) {
        $file = path_upload_attachment . $attachment['new_name'];
        is_file($file) and unlink($file);
        send_message($attachment['user_id'], '你的附件【' . $attachment['old_name'] . '】被管理员删除了');
        success('删除附件成功');
    } else {
        error('删除附件失败');
    }

} else if ($action == 'modify') {
    /**
     * 附件修改
     */
    if (is_post || is_ajax) {
        $fid = input(2);
        $data = array(
            'old_name' => input('old'),
            'price' => intval(input('price'))
        );
        $sql = sprintf('update wlz_attachment set %s where id="%s"', db_str_concat($data), $fid);
        if (db_query($sql)) {
            success('修改附件成功', 'attachment.html');
        } else {
            error('修改附件失败');
        }

    } else {
        $fid = input(2);
        $attachment = db_find('select * from wlz_attachment where id="' . $fid . '"');
        empty($attachment) and error('你要修改的附件不存在');
        $title = '附件修改';
        include './view/attachment_modify.html';
    }


} else {
    $page = $action;
    $count = db_field('select count(*) from wlz_attachment');
    $page_show = pages('attachment-%s.html', $count, $page, config('page_num'), $limit);
    $attachments = db_select('select * from wlz_attachment order by create_time desc limit ' . $limit);
    foreach ($attachments as $key => $value) {
        $attachments[$key]['create_time'] = format_time($value['create_time']);
        $attachments[$key]['size'] = format_size($value['size']);
        $attachments[$key]['new_name'] = url_upload_attachment . $value['new_name'];
    }
    $title = '附件管理';
    include './view/attachment.html';
}