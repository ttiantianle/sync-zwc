<?php
/**
 *
 * $demo = array(
'dbhost' => 'localhost',
'dbuser' => 'vpu_user',
'dbport' => '3306',
'dbpwd' => 'vpubao!@#123',
'dbname' => 'b2b2c_xxxx',
'dbcharset' => 'utf8'
)
 */
namespace ttiantianle\sync;

use ttiantianle\sync\Mysql;
class Sync{

    private $link = null;

    public function __construct($dbConfig=[])
    {
       if ($config=$this->validateDbConfig($dbConfig)){
           $this->link = new Mysql($config);
       }
    }

    public function addOneToNewDataBase($table='',$oldData=[],$keyMap=[],$primary=[]){
        $data = Tools::arrayExtract($oldData,$keyMap);
        $where ='';
        foreach ($primary as $v){
            $where .= " ".$v."='".$data[$v]."' and";
        }
        $where = substr($where,0,-3);
        $query = $this->link->query_result();
        if ($query){
            return 1;
        }
    }







    private function validateDbConfig($dbConfig=[]){
      if (!is_array($dbConfig)) return false;
      if (!isset($dbConfig['dbname'])) return false;
       $config = array(
            'dbhost' => isset($dbConfig['dbhost']) ? $dbConfig['dbhost'] : 'localhost',
            'dbuser' => isset($dbConfig['dbuser']) ? $dbConfig['dbuser'] : 'root',
            'dbport' => isset($dbConfig['dbport']) ? $dbConfig['dbport'] : '3306',
            'dbpwd'  => isset($dbConfig['dbpwd']) ? $dbConfig['dbpwd'] : '',
            'dbname' => isset($dbConfig['dbname']) ? $dbConfig['dbname'] : '',
            'dbcharset' => isset($dbConfig['dbcharset']) ? $dbConfig['dbcharset'] : 'utf8',
        );
        return $config;

    }
}