<?php
namespace ttiantianle\sync;
use Exception;
/**
 * SQL functions library for PHP7
 */
class Mysql7
{
    public $link_id = 0;

    public $debug = true;

    protected $dbconfig = array();

    public $field_arr = array();

    public $error = "";
    public $errno = 0;

    /**
     * Class constructor
     *
     * @param array $dbconfig Database config
     */
    function __construct($dbconfig = null)
    {
        $this->dbconfig = $dbconfig;
    }

    function set_config($dbconfig)
    {
        $this->dbconfig = $dbconfig;
    }

    function get_server()
    {
        return $this->dbconfig['dbhost'];
    }


    /**
     * Connect to the MySQL Server
     * @param bool|false $force
     * @return bool
     * @throws Exception
     */
    function connect($force = false)
    {
        if (!$force && $this->link_id) {
            if ($this->dbconfig['dbname'] != '')
                $ret = @mysqli_select_db( $this->link_id,$this->dbconfig['dbname']);
            return $ret;
        }

        if ($force && $this->link_id) $this->close();

        $this->link_id = @mysqli_connect($this->dbconfig['dbhost'],$this->dbconfig['dbuser'],
            $this->dbconfig['dbpwd'],$this->dbconfig['dbname'],$this->dbconfig['dbport']);


        if (!$this->link_id) {
//            $this->log("connet db failed", true);
            $this->errno = @mysqli_connect_errno();
            $this->error = @mysqli_connect_error();
            throw new Exception($this->error, $this->errno);
        }

        $qid = @mysqli_query($this->link_id,"set names ".$this->dbconfig['dbcharset']);

        if (!$qid) {
//            $this->log("set names failed", true);
            $this->errno = @mysqli_errno($this->link_id);
            $this->error = @mysqli_error($this->link_id);
            throw new Exception($this->error, $this->errno);
        }
        if ($this->dbconfig['dbname'] != '') {
            $ret = @mysqli_select_db( $this->link_id,$this->dbconfig['dbname']);

            if (!$ret) {
//                $this->log("mysqli_select_db failed,db name:{$this->dbconfig['dbname']}", true);
                $this->errno = @mysqli_errno($this->link_id);
                $this->error = @mysqli_error($this->link_id);
                throw new Exception($this->error, $this->errno);
            }
        }
        return true;
    }

    function select_db($dbname)
    {
        $this->connect();
        $this->dbconfig['dbname'] = $dbname;
        $ret = @mysqli_select_db( $this->link_id,$this->dbconfig['dbname']);
        if (!$ret) {
//            $this->log("mysqli_select_db failed,db name:{$this->dbconfig['dbname']}", true);
            $this->errno = @mysqli_errno($this->link_id);
            $this->error = @mysqli_error($this->link_id);
            throw new Exception($this->error, $this->errno);
        }
        return $ret;
    }

    /**
     * Queries the SQL server, returning a single row
     *
     * @param string $string SQL Query.
     * @return array or false
     */
    function query_result($string)
    {
        $params = func_get_args();
        $id = call_user_func_array(array($this, 'query'), $params);
        if ($id) {
            $this->field_arr = array();
            $nums = $this->num_fields($id);
            for ($i = 0; $i < $nums; $i++) {
                $this->field_arr[] = $this->field_name($id, $i);
            }
            $arr = array();
            while ($result = $this->fetch_array($id)) {
                $arr[] = $result;
            }
            $this->free_result($id);
            $this->lastquery = $string;
            return $arr;
        }
        return false;
    }

    /**
     * Queries the SQL server, returning the defined (or first) select value
     * from the first row.
     *
     * @param string $string SQL Query.
     * @param string $value Optional select field name to return.
     * @return array or false
     */
    function query_result_single($string, $def = false)
    {
        $params = func_get_args();
        $id = call_user_func_array(array($this, 'query'), $params);
        if ($id) {
            $this->field_arr = array();
            $nums = $this->num_fields($id);
            for ($i = 0; $i < $nums; $i++) {
                $this->field_arr[] = $this->field_name($id, $i);
            }

            $result = $this->fetch_array($id);
            $this->free_result($id);
            $this->lastquery = $string;
            return $result === false ? array() : array($result);
        }
        return false;
    }

    /**
     * 插入数据表
     * @param array $arr 数据数组, format: array('field1'=>'val1',...)
     * @param string $tbl 表名
     *
     * @return resource or false
     */
    function insert(array $arr, $tbl)
    {
        $all_params = func_get_args();
        if (empty($arr)) {
//            $this->log('insert no valid field to insert, params:' . print_r($all_params, true), true);
            return false;
        }
        $t_arr = array();
        $field_names = array();
        $table_desc = $this->query_result("DESC " . $tbl);
        foreach ((array)$table_desc as $table_item) {
            foreach ($arr as $k => $v) {
                if (strtolower($table_item['Field']) == strtolower($k)) {
                    $t_arr[$k] = $arr[$k] . "";
                }
            }
        }
        $arr = $t_arr;
        if (empty($arr)) {
//            $this->log('insert no valid field to insert, params:' . print_r($all_params, true), true);
            return false;
        }

        $fields = array_keys($arr);
        $vals = array_values($arr);
        $sql = "insert into {$tbl}(`" . implode("`,`", $fields) . "`) values(";
        for ($i = 0; $i < count($vals); $i++) {
            if (isset($vals[$i]))
                $sql .= "'" . $this->escape_string($vals[$i]) . "'";
            else
                $sql .= "null";
            if ($i < (count($vals) - 1)) $sql .= ",";
        }
        $sql .= ")";
        return $this->query($sql);
    }

    /**
     * 请慎用，仅用于进行字段四则运算，插入数据表
     * @param array $arr 数据数组, format: array('field1'=>'val1',...)
     * @param string $tbl 表名
     *
     * @return resource or false
     */
    function insert_decimal(array $arr, $tbl)
    {
        $all_params = func_get_args();
        if (empty($arr)) {
//            $this->log('insert no valid field to insert, params:' . print_r($all_params, true), true);
            return false;
        }
        $t_arr = array();
        $field_names = array();
        $table_desc = $this->query_result("DESC " . $tbl);
        foreach ((array)$table_desc as $table_item) {
            foreach ($arr as $k => $v) {
                if (strtolower($table_item['Field']) == strtolower($k)) {
                    $t_arr[$k] = $arr[$k] . "";
                }
            }
        }
        $arr = $t_arr;
        if (empty($arr)) {
//            $this->log('insert no valid field to insert, params:' . print_r($all_params, true), true);
            return false;
        }

        $fields = array_keys($arr);
        $vals = array_values($arr);
        $sql = "insert into {$tbl}(`" . implode("`,`", $fields) . "`) values(";
        for ($i = 0; $i < count($vals); $i++) {
            if (isset($vals[$i]))
                $sql .= $vals[$i];
            else
                $sql .= "null";
            if ($i < (count($vals) - 1)) $sql .= ",";
        }
        $sql .= ")";
        return $this->query($sql);
    }

    /**
     * 更新数据表
     * @param array $arr 数据数组, format: array('field1'=>'val1',...)
     * @param string $tbl 表名
     * @param string $where 表名
     * @param {string | integer} $param1 first parameter
     * @param {string | integer} $param2 second parameter
     * @return resource or false
     */
    function update(array $arr, $tbl, $where, $where_params = null)
    {
        $all_params = func_get_args();
        if (empty($arr)) {
//            $this->log('update no valid field to update, params:' . print_r($all_params, true), true);
            return false;
        }
        $t_arr = array();
        $field_names = array();
        $table_desc = $this->query_result("DESC " . $tbl);
        foreach ((array)$table_desc as $table_item) {
            foreach ($arr as $k => $v) {
                if (strtolower($table_item['Field']) == strtolower($k)) {
                    $t_arr[$k] = $arr[$k];
                }
            }
        }
        $arr = $t_arr;
        if (empty($arr)) {
//            $this->log('update no valid field to update, params:' . print_r($all_params, true), true);
            return false;
        }

        $sql = "update {$tbl} set ";
        $i = 0;
        $len = count($arr);
        foreach ($arr as $key => $val) {
            if (isset($val))
                $sql .= "`{$key}`= '" . $this->escape_string($val) . "'";
            else
                $sql .= "`{$key}`= null";
            if ($i < ($len - 1)) $sql .= ", ";
            $i++;
        }
        $sql .= " " . $where;
        $params = func_get_args();
        $params = array_slice($params, 3);
        return $this->query($sql, $params);
    }

    /**
     * 请慎用！！！ 数据字段安全性检查需要外部 调用  '" . $this->escape_string($val) ."'"
     * 财务数据——更新数据表
     * @param array $arr 数据数组, format: array('field1'=>'val1',...)
     * @param string $tbl 表名
     * @param string $where 表名
     * @param {string | integer} $param1 first parameter
     * @param {string | integer} $param2 second parameter
     * @return resource or false
     */
    function update_decimal(array $arr, $tbl, $where, $where_params = null)
    {
        $all_params = func_get_args();
        if (empty($arr)) {
//            $this->log('update no valid field to update, params:' . print_r($all_params, true), true);
            return false;
        }
        $t_arr = array();
        $field_names = array();
        $table_desc = $this->query_result("DESC " . $tbl);
        foreach ((array)$table_desc as $table_item) {
            foreach ($arr as $k => $v) {
                if (strtolower($table_item['Field']) == strtolower($k)) {
                    $t_arr[$k] = $arr[$k];
                }
            }
        }
        $arr = $t_arr;
        if (empty($arr)) {
//            $this->log('update no valid field to update, params:' . print_r($all_params, true), true);
            return false;
        }

        $sql = "update {$tbl} set ";
        $i = 0;
        $len = count($arr);
        foreach ($arr as $key => $val) {
            if (isset($val))
                $sql .= "`{$key}`= " . $val;
            else
                $sql .= "`{$key}`= null";
            if ($i < ($len - 1)) $sql .= ", ";
            $i++;
        }
        $sql .= " " . $where;
        $params = func_get_args();
        $params = array_slice($params, 3);
        return $this->query($sql, $params);
    }


    /**
     * Queries the SQL server. Allows you to use sprintf-like
     * format and automatically escapes variables.
     * <pre>
     *    $db->query('SELECT %s FROM %s WHERE myfield = %s, 'field_name', 'table_name', 5);
     * </pre>
     *
     * @param string $string
     * @param {string | integer} $param1 first parameter
     * @param {string | integer} $param2 second parameter
     * @param {string | integer} $param3 ...
     * @return unknown Resource ID
     */
    function query($string, $params = null)
    {
        $this->connect();
        if (!is_array($params)) {
            $params = func_get_args();
            $params = array_slice($params, 1);
        }
        if (count($params)) {
            foreach ($params as $key => $value) {
                $params[$key] = $this->prepare_param($value);
            }
            $string = vsprintf($string, $params);
            if ($string == false);
//                $this->log('Invalid sprintf: ' . $string . "\n" . 'Arguments: ' . implode(', ', $params), true);
        }
        $timing = microtime(true);
        $id = $this->execute($string, $this->link_id);
        $timing = (int)((microtime(true) - $timing) * 1000);

        $this->lastquery = $string;

        return $id;
    }

    /**
     * Internal handler for parameters. Returns an
     * escaped parameter.
     *
     * @param {string | integer} $param
     * @return {string | integer} Escaped parameter.
     */
    function prepare_param($param)
    {
        if ($param === null) return 'NULL';
        elseif (is_integer($param)) return $param;
        elseif (is_bool($param)) return $param ? 1 : 0;
        return "'" . $this->escape_string($param) . "'";
    }

    function execute($sql)
    {
        $this->connect();
//        $this->log("SQL Query: {$sql}");
        if (strpos($sql, "shop_id=0") !== false) {
            $arr = debug_backtrace();
//            $this->log("SQL Query stack:".print_r($arr,true));
        }
        $result = @mysqli_query( $this->link_id,$sql);
        if ($result) return $result;
//        $this->log('SQL ERR: ' . $sql, true);

        return false;
    }

    function multi_query($sql)
    {
        return NULL;
    }

    function fetch_array($id)
    {
        return @mysqli_fetch_array($id,MYSQLI_ASSOC);
    }


    function fetch_object($id)
    {
        return @mysqli_fetch_object($id);
    }

    function free_result($id)
    {
        @mysqli_free_result($id);
    }

    function num_rows($id)
    {
        parent::num_rows($id);
        return @mysqli_num_rows($id);
    }

    function num_fields($id)
    {
        return @mysqli_num_fields($id);
    }

    function field_name($id, $num)
    {
        $obj = @mysqli_fetch_field($id);
//        $obj = @mysql_fetch_field($id, $num);
        return $obj->name;
    }

    function insert_id()
    {
        return @mysqli_insert_id($this->link_id);
    }

    function close()
    {
        @mysqli_close($this->link_id);
        $this->link_id = null;
    }

    function escape_string($string)
    {
        $this->connect();
        return @mysqli_real_escape_string($this->link_id,$string);
    }

    /**
     * @param array $arr
     * @param int $val_type value type, 0 string, 1 int, 2 float, 3 double
     * @return string
     */
    function create_in(array $arr, $val_type = 0)
    {
        $t_arr = array();
        for ($i = 0; $i < count($arr); $i++) {
            switch ($val_type) {
                case 1: {
                    $t_arr[$i] = intval($arr[$i]);
                    break;
                }
                case 2: {
                    $t_arr[$i] = floatval($arr[$i]);
                    break;
                }
                case 3: {
                    $t_arr[$i] = doubleval($arr[$i]);
                    break;
                }
                case 0:
                default:
                    $t_arr[$i] = "'" . $this->escape_string($arr[$i]) . "'";
            }
        }
        return "(" . implode(",", $t_arr) . ")";
    }

    function ping()
    {
        return @mysqli_ping($this->link_id);
    }

//    function log($message, $is_err = false)
//    {
//        if ($this->link_id) {
//            if ($is_err) {
//                $this->errno = @mysqli_errno($this->link_id);
//                $this->error = @mysqli_error($this->link_id);
//                $message .= "\tNo: " . $this->errno . ' error:' . $this->error . "\n";
//                if ($GLOBALS['_CFG']['echo_mysql_error_msg']) {
//                    //echo "<p>{$message}</p>";
//                }
//            }
//            Log::LOG('sql.log', "{$message}");
//        }
//    }

    function errno()
    {
        return $this->errno;
    }

    function error()
    {
        return $this->error;
    }

    function get_field_arr()
    {
        return $this->field_arr;
    }

}
