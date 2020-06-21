<?php
namespace ttiantianle\sync;

class Code{
    const NODATA    = 1000;
    const LINKERROR = 1001;
    const ADDERROR  = 1002;
    const HADEXISTS = 1003;

    public static $codeConf = [
      self::NODATA      => '没有数据',
      self::LINKERROR   => '连接失败',
      self::ADDERROR    => '添加数据失败',
      self::HADEXISTS   => "数据已存在",
    ];
}