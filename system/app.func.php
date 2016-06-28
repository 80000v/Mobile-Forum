<?php
/*
 * 应用级别的函数
 */

//发送消息
function send_message($user_id, $message)
{
    $data = array(
        'user_id' => intval($user_id),
        'content' => $message,
        'create_time' => time()
    );
    //此处没有必要去判断消息是否发送成功
    db_insert('wlz_message', $data);
}


//判断是否是版主
function is_moder($user_id, $section_id)
{
    $sql = sprintf('select id from wlz_moderator where user_id="%s" and section_id="%s"', $user_id, $section_id);
    return !!db_find($sql);
}

//积分操作
function credits_modify($user_id, $credits)
{
    $sql = sprintf('update wlz_user set credits=credits%s where id="%s"', $credits, $user_id);
    return db_query($sql);
}





























