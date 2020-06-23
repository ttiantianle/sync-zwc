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

class Sync{

    private $link = null;

    public function __construct($dbConfig=[])
    {
       if ($config=$this->validateDbConfig($dbConfig)){
           $this->link = new \ttiantianle\sync\Mysql($config);
       }
    }

    public function setDbConfig($dbConfig=[]){
        if ($config=$this->validateDbConfig($dbConfig)){
            $this->link = new Mysql($config);
        }
    }
    public function addOneToNewDb($table='',$oldData=[],$keyMap=[],$primary=[]){
        if ($this->link===null){
            return Code::UNLINK;
        }
        $data = Tools::arrayExtract($oldData,$keyMap);
        if (!empty($primary)){//如果没有条件，直接插入
            $where ='';
            foreach ($primary as $v){
                $where .= " ".$keyMap[$v]."='".$oldData[$v]."' and";
            }
            $where = substr($where,0,-3);
            $query = $this->link->query_result("select * from ".$table.' where '.$where);
            if ($query){
                return Code::HADEXISTS;
            }
        }

        $res = $this->link->insert($data,$table);
        if ($res){
            return Code::SUCCESS;
        }else{
            return Code::SYNCERROR;
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