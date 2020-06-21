<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/3/6
 * Time: 16:29
 */
namespace ttiantianle\sync;

class Tools
{
   public static function arrayExtract($data, $keys){
        if(!is_array($keys) || !$keys) return [];
        $res = [];
        foreach ($keys as $k=>$key){
            if(is_numeric($k)) $k = $key;
            if(!isset($data[$k])) continue;
            $res[$key] = $data[$k];
        }
        return $res;
    }

    /**
     * type 取值123  1数字 2小写字母数字 3大小写字母数字 4 所有
     * @param int $length
     * @param int $type
     * @return string
     */
   public static function makePassword($length = 8,$type=4)
    {
        // 密码字符集，可任意添加你需要的字符
        if ($type==1){
            $chars=array(
                '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'
            );
        }else if ($type == 2){
            $chars = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h',
                'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's',
                't', 'u', 'v', 'w', 'x', 'y', 'z',
                '0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
        }else if ($type == 3){
            $chars = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h',
                'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's',
                't', 'u', 'v', 'w', 'x', 'y', 'z', 'A', 'B', 'C', 'D',
                'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O',
                'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
                '0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
        }else{
            $chars = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h',
                'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's',
                't', 'u', 'v', 'w', 'x', 'y', 'z', 'A', 'B', 'C', 'D',
                'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O',
                'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
                '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '!',
                '@', '#', '$', '%', '^', '&', '*', '(', ')', '-', '_',
                '[', ']', '{', '}', '<', '>', '~', '`', '+', '=', ',',
                '.', ';', ':', '/', '?', '|');
        }

        // 在 $chars 中随机取 $length 个数组元素键名
        $keys = array_rand($chars, $length);
        $password = '';
        for ($i = 0; $i < $length; $i++) {
            // 将 $length 个数组元素连接成字符串
            $password .= $chars[$keys[$i]];
        }
        return $password;
    }

    /**
     * 计算两点地理坐标之间的距离
     * @param  Decimal $longitude1 起点经度
     * @param  Decimal $latitude1  起点纬度
     * @param  Decimal $longitude2 终点经度
     * @param  Decimal $latitude2  终点纬度
     * @param  Int     $unit       单位 1:米 2:公里
     * @param  Int     $decimal    精度 保留小数位数
     * @return Decimal
     */
    static function getDistance($longitude1, $latitude1, $longitude2, $latitude2, $unit=2, $decimal=2){

        $EARTH_RADIUS = 6370.996; // 地球半径系数
        $PI = 3.1415926;

        $radLat1 = $latitude1 * $PI / 180.0;
        $radLat2 = $latitude2 * $PI / 180.0;

        $radLng1 = $longitude1 * $PI / 180.0;
        $radLng2 = $longitude2 * $PI /180.0;

        $a = $radLat1 - $radLat2;
        $b = $radLng1 - $radLng2;

        $distance = 2 * asin(sqrt(pow(sin($a/2),2) + cos($radLat1) * cos($radLat2) * pow(sin($b/2),2)));
        $distance = $distance * $EARTH_RADIUS * 1000;

        if($unit==2){
            $distance = $distance / 1000;
        }

        return round($distance, $decimal);

    }

    //获取经纬度
    static public function getLonLatByIP($ip='106.9.71.42')
    {
        if(empty($ip))
        {
            return false;
        }
        $content = file_get_contents("http://api.map.baidu.com/location/ip?ak=Lz6i4kL6YdDucGGTGt9pyereomRTYyTo&ip=$ip&coor=bd09ll");
        $arr= json_decode($content,true);
        if (!isset($arr['content'])) return false;
        $lng=$arr['content']['point']['x'];//提取经度数据
        $lat=$arr['content']['point']['y'];;//提取纬度数据
        return array(
            "lon"=>$lng,
            "lat" => $lat
        );
    }
}