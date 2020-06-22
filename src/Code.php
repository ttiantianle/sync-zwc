<?php
namespace ttiantianle\sync;

class Code{
    const NODATA    =   1000;
    const LINKERROR =   1001;
    const SYNCERROR =   1002;
    const HADEXISTS =   1003;
    const UNKNOW    =   3000;
    const SUCCESS   =   200;
    const UNLINK    =   1004;

    public static $codeErrorConf = [
      self::NODATA      =>  '没有数据',
      self::LINKERROR   =>  '数据库连接错误',
      self::SYNCERROR   =>  '数据同步错误',
      self::HADEXISTS   =>  '数据已存在',
      self::UNKNOW      =>  '未知错误',
      self::UNLINK      =>  '数据库未连接',
    ];

    public static $codeSuccessConf = [
      self::SUCCESS     => '同步成功'
    ];
}