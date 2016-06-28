<?php
$action = input(1);
$uid = session('uid') or redirect('visit-login.html', 3, '你还没有登录，3秒后跳转...');

if ($action == 'download') {

    $file = db_find('select * from wlz_attachment where id="' . input(2) . '"') or error('附件不存在');
    $filename = path_upload_attachment . $file['new_name'];
    file_exists($filename) or error('附件源文件找不到了');
    //下载别人的附件，需要扣金币
    if ($uid != $file['user_id']) {
        //扣积分，同一天下载的可以不扣积分。
        $sql = sprintf('select * from wlz_download where attachment_id="%s" and user_id="%s"', $file['id'], $uid);
        $result = db_find($sql);
        //如果没有下载此文件的记录，扣金币
        if (empty($result)) {
            //开启事务
            db_transaction();
            $user = db_find('select id,credits from wlz_user where id="' . $uid . '" for update');
            if ($user['credits'] < $file['price']) {
                db_rollback();
                error('你的积分不够下载此文件');
            }
            $query1 = db_query('update wlz_user set credits=credits-' . $file['price'] . ' where id="' . $uid . '"');
            $query2 = db_query('update wlz_user set credits=credits+' . $file['price'] . ' where id="' . $file['user_id'] . '"');
            $query3 = db_insert('wlz_download', array(
                'attachment_id' => $file['id'],
                'user_id' => $uid,
                'create_time' => time()
            ));
            if ($query1 && $query2 && $query3) {
                db_commit();//提交事务
                //给文件所属用户发送内信
                $content = '你的附件<a href="attachment-' . $file['id'] . '.html"><i class="icon icon-attachment"></i>' . $file['old_name'] . '</a>被下载，获得' . $file['price'] . '积分';
                send_message($file['user_id'], $content);
            } else {
                db_rollback();
                error('文件下载失败');
            }
        }
    }

    //输出文件
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename=' . $file['old_name']);
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . $file['size']);
    readfile($filename);

} else if ($action == 'upload') {
    /**
     * 上传附件
     */
    is_post or is_ajax or redirect('index.html');
    $price = input('price', 0);

    //上传附件
    $result = upload_file(current($_FILES), path_upload_attachment,config('upload_attachment_ext'));
    //上传成功
    if ($result[0]) {
        $data = array(
            'user_id' => $uid,
            'old_name' => db_str_escape($result[1]['old_name']),
            'new_name' => db_str_escape($result[1]['new_name']),
            'size' => $result[1]['size'],
            'price' => intval($price),
            'create_time' => time()
        );
        if (db_insert('wlz_attachment', $data)) {
            success('上传附件成功', '', array('text' => '[attachment=' . db_insert_id() . ']' . $data['old_name'] . '[/attachment]', 'price' => $price));
        } else {
            error('上传附件失败');
        }
    }else{
        error($result[1]);
    }
} else {
    /**
     * 显示附件的详情
     */
    //$action 的值就是附件的id
    $file = db_find('select wlz_attachment.*,username from wlz_attachment left join wlz_user on wlz_attachment.user_id=wlz_user.id where wlz_attachment.id="' . $action . '"') or error('附件不存在');
    $user = db_find('select id,credits from wlz_user where id="' . $uid . '"');
    $file['size'] = get_size($file['size']);
    $title = '附件下载';
    include './view/attachment.html';
}