<?php
/**
 * 新建测试数据库sync,测试数据表user ，字段id username varchar,age int,sex tinyint
 *  composer install
 */
require_once "vendor/autoload.php";

$config = array(
    'dbhost' => 'localhost',
    'dbuser' => 'root',
    'dbport' => '3306',
    'dbpwd' => '',
    'dbname' => 'sync',
    'dbcharset' => 'utf8'
);

$sync = new \ttiantianle\sync\Sync($config);
$table = 'user';
$oldData = [
    'title'     => '小明',
    'xingbie'   => '1',
    'age'       => 23
];
$keyMap = [
    'title'    => 'username',
    'xingbie'   => 'sex',
    'age'
];
$primary = ['title','xingbie'];
$res = $sync->addOneToNewDb($table,$oldData,$keyMap,$primary);
var_dump($res);die;