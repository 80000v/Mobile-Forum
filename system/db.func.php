<?php
/*
 *
 * 数据库相关函数
 * 万林赞 2014.4.15
 */


/**
 * @return mysqli
 * 连接数据库
 */
function db_connect()
{
    static $conn = null;
    if (is_null($conn)) {
        $conn = mysqli_connect(config('db_host'), config('db_user'), config('db_pwd'), config('db_name'));
        if (mysqli_connect_errno()) {
            error('数据库连接失败' . mysqli_connect_errno() . ':' . mysqli_connect_error());
        }
        /**
         * 这种方式不会使用到mysqli_real_escape_string()函数
         * mysqli_query($conn,'set names utf8');
         */
        mysqli_set_charset($conn,'utf8');
    }
    return $conn;
}


/**
 * @param $sql
 * @return bool|mysqli_result
 * 执行成功返回true或者result ,失败返回false
 */
function db_query($sql)
{
    return mysqli_query(db_connect(), $sql);
}

/**
 * @param $sql
 * @return array|bool
 * 执行成功返回二维数组或一维空数组 ，失败返回false
 */
function db_select($sql)
{
    $result = db_query($sql);
    if (is_object($result)) {
        $rows = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        mysqli_free_result($result);
        return $rows;
    }
    return false;
}

/**
 * @param $sql
 * @return array|bool
 * 执行成功返回一维数组，失败返回false
 */
function db_find($sql)
{
    $array = db_select($sql);
    if (is_array($array)) {
        return empty($array) ? $array : current($array);
    }
    return false;
}


/**
 * @param $sql
 * @return null|string|bool
 * 执行成功返回字符串或null，失败返回false
 */
function db_field($sql)
{
    $result = db_query($sql);
    if (is_object($result)) {
        $row = mysqli_fetch_row($result);
        return is_null($row) ? $row : current($row);
    }
    return false;
}

/**
 * @param $table
 * @param $array
 * @param string $type insert or replace
 * @return bool
 * 插入一条或多条数据
 */
function db_insert($table, $array, $type = 'insert')
{
    //统一为二维数组处理
    if (!is_array(current($array))) {
        $arr = $array;
        unset($array);
        $array[] = $arr;
    }
    //拼接SQL语句
    $sql = sprintf('%s into %s (%s) values ', $type, $table, db_str_concat(array_keys(current($array)), '`'));
    foreach ($array as $v) {
        $sql .= sprintf('(%s),', db_str_concat($v, "'"));
    }
    $sql = substr($sql, 0, -1);
    return db_query($sql);
}

/**
 * @return mixed
 * 返回插入数据的id
 */
function db_insert_id()
{
    return db_connect()->insert_id;
}

/**
 * @return int
 * 返回最后sql影响条数
 */
function db_affected_rows()
{
    return db_connect()->affected_rows;
}


/**
 * @return void
 * 开始事务
 */
function db_transaction()
{
    db_query("start transaction");
}

/**
 * @return void
 * 提交事务
 */
function db_commit()
{
    db_query("commit");
}


/**
 * @param string $name
 * @return void
 * 保存事务点
 */
function db_savepoint($name)
{
    db_query("savepoint {$name}");
}


/**
 * @param $point
 * @return void
 * 回滚事务
 */
function db_rollback($point = null)
{
    $sql = "rollback";
    if (!is_null($point)) {
        $sql .= " savepoint {$point}";
    }
    db_query($sql);
}


/**
 * @param $str
 * @return string
 * 转义字符串
 */
function db_str_escape($str)
{
    if (get_magic_quotes_gpc()) {
        $str = stripslashes($str);
    }
    return mysqli_real_escape_string(db_connect(), $str);
}

/**
 * @param $array
 * @param string $tag
 * @return string
 * 拼接字符串，只支持一维数组
 * `name`='wanlinzan'   `fsd`,`sfdsd`,`sdfsd`   'fsdds','fsdf','fds'
 */
function db_str_concat($array, $tag = '=')
{
    $str = '';
    if ($tag == '=') {
        foreach ($array as $k => $v) {
            if (is_null($v)) {
                $str .= sprintf("`%s`=null,", $k);
            } else {
                $str .= sprintf("`%s`='%s',", $k, $v);
            }
        }
        return substr($str, 0, -1);
    } else {
        foreach ($array as $v) {
            if (is_null($v)) {
                $str .= 'null,';
            } else {
                $str .= $tag . $v . $tag . ',';
            }
        }
        return substr($str, 0, -1);
    }
}

/**
 * @param $table
 * @param $id
 * @return bool
 * 通过主键判断表中的数据是否存在
 */
function db_data_exists($table, $id)
{
    $sql = sprintf('select id from %s where id="%s"', $table, $id);
    return !!db_find($sql);
}





