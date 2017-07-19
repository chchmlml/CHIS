<?php

namespace app\library;
/**
 * Class     Util
 *
 * @author   haicheng
 */
class Util {

    static $_service_holder = array();

    static $_model_holder = array();

    /**
     * Method  x
     * 调试方法
     *
     * @author haicheng
     */
    static function x() {
        $argument_list = func_get_args();

        $called = debug_backtrace();

        echo '<pre>' . PHP_EOL;

        foreach ($argument_list as $variable) {

            echo '<strong>' . $called[0]['file'] . ' (line ' . $called[0]['line'] . ')</strong> ' . PHP_EOL;

            if (is_array($variable)) {
                print_r($variable);
            } else {
                var_dump($variable);
            }

            echo PHP_EOL;
        }

        echo '</pre>' . PHP_EOL;
        exit();
    }

    /**
     * Method  underlineToCamel
     * 下划线转驼峰
     *
     * @author haicheng wenyue1
     * @static
     *
     * @param string $string
     * @param bool   $is_ignore_uppercase
     *
     * @return string
     */
    static function underlineToCamel($string, $is_ignore_uppercase = false) {
        if (false === $is_ignore_uppercase) {
            return preg_replace_callback('/_([a-zA-Z])/', function ($m) {
                return strtoupper($m[1]);
            }, $string);
        } else {
            return preg_replace_callback('/_([a-z])/', function ($m) {
                return strtoupper($m[1]);
            }, $string);
        }
    }

    /**
     * Method  camelToUnderline
     * 驼峰转下划线
     *
     * @author haicheng
     * @static
     *
     * @param $string
     *
     * @return string
     */
    static function camelToUnderline($string) {
        return strtolower(preg_replace('/(?!^)(?=[A-Z])/', '_', $string));
    }

    /**
     * get_server_ip
     * 获取当前server ip
     *
     * @author haicheng wenyue1
     * @return string
     */
    static function getServerIp() {
        if (isset($_SERVER['WEIBO_ADINF_SERVERIP'])) {
            return $_SERVER['WEIBO_ADINF_SERVERIP'];
        } elseif (isset($_SERVER['SINASRV_INTIP'])) { // 动态平台环境变量
            return $_SERVER['SINASRV_INTIP'];
        } elseif (isset($_SERVER['SERVER_ADDR'])) {
            return $_SERVER['SERVER_ADDR'];
        }

        return php_uname('n');
    }

    /**
     * Method  isUrl
     * 验证URL
     *
     * @author haicheng
     *
     * @param string $variable
     *
     * @return bool
     */
    static function isUrl($variable = '') {

        $pattern = '/^((https|http|)?:[\/\/]{2})[a-zA-Z0-9]+.[^\s]+/is';

        if (preg_match($pattern, trim($variable))) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * Method  getChineseStringLength
     * 获取中文字符长度
     *
     * @author haicheng
     *
     * @param $string
     *
     * @return int
     */
    static function getChineseStringLength($string) {
        $string = trim($string);

        if ('' === $string) {
            return 0;
        }

        $string_length = mb_strlen($string, 'UTF-8');

        $chinese_string_length = mb_strlen(preg_replace('/[0-9a-z\s]+/is', '', $string), 'UTF-8');

        if ($string_length === $chinese_string_length) {
            return $string_length;
        }

        return $chinese_string_length + ceil(($string_length - $chinese_string_length) / 2);
    }

    /**
     * Method  getClientIp
     * 获取客户端IP
     *
     * @author haicheng
     * @static
     * @return bool|string
     */
    static function getClientIp() {
        //验证HTTP头中是否有REMOTE_ADDR
        if (!isset($_SERVER['REMOTE_ADDR'])) {
            return '127.0.0.1';
        }

        //验证是否为非私有IP
        if (filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE)) {
            return $_SERVER['REMOTE_ADDR'];
        }

        //验证HTTP头中是否有HTTP_CIP
        if (isset($_SERVER['HTTP_CIP'])) {
            return $_SERVER['HTTP_CIP'];
        }

        //验证HTTP头中是否有HTTP_X_FORWARDED_FOR
        if (!isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['REMOTE_ADDR'];
        }

        //定义客户端IP
        $client_ip = '';

        //获取", "的位置
        $position = strrpos($_SERVER['HTTP_X_FORWARDED_FOR'], ', ');

        //验证$position
        if (false === $position) {
            $client_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $client_ip = substr($_SERVER['HTTP_X_FORWARDED_FOR'], $position + 2);
        }

        //验证$client_ip是否为合法IP
        if (filter_var($client_ip, FILTER_VALIDATE_IP)) {
            return $client_ip;
        } else {
            return false;
        }
    }

    /**
     * Method  isEqualsForNumber
     * 比较两个数是否相等
     *
     * @author haicheng
     * @static
     *
     * @param $variable1
     * @param $variable2
     *
     * @return bool
     */
    static function isEqualsForNumber($variable1, $variable2) {
        return abs($variable1 - $variable2) < 0.0000000001;
    }

    /**
     * parsePicIdFromImageUrl
     * 从图片url里面获取pid id
     *
     * @author haicheng
     * @return bool
     *
     * @param string $photo
     */
    static function parsePicIdFromImageUrl($photo = '') {

        preg_match('/[0-9a-zA-Z]{21,40}/i', $photo, $match_pic_id);
        if (empty($match_pic_id)) {
            return false;
        }

        return $match_pic_id[0];
    }

    /**
     * 将mid从10进制转换成62进制字符串
     *
     * @param    string $mid
     *
     * @return    string
     */
    static function from10to62($mid) {
        $str      = "";
        $midlen   = strlen($mid);
        $segments = ceil($midlen / self::$encodeBlockSize);
        $start    = $midlen;
        for ($i = 1; $i < $segments; $i += 1) {
            $start -= self::$encodeBlockSize;
            $seg = substr($mid, $start, self::$encodeBlockSize);
            $seg = self::encodeSegment($seg);
            $str = str_pad($seg, self::$decodeBlockSize, '0', STR_PAD_LEFT) . $str;
        }
        $str = self::encodeSegment(substr($mid, 0, $start)) . $str;

        return $str;
    }


    /**
     * 将10进制转换成62进制
     *
     * @param    string $str 10进制字符串
     *
     * @return    string
     */
    private static function encodeSegment($str) {
        $out = '';
        while ($str > 0) {
            $idx = $str % 62;
            $out = substr(self::$string, $idx, 1) . $out;
            $str = floor($str / 62);
        }

        return $out;
    }

    /**
     * Method  arrayKeySort
     * 根据指定key对二维数组重排序
     *
     * @author guangling1<guangling1@staff.weibo.com>
     * @static
     *
     * @param array  $arr
     * @param string $key
     * @param string $sort
     *
     * @return array
     */
    static function arrayKeySort(array $arr, $key, $sort = 'DESC') {
        $keys_value = array();
        $new_array  = array();
        if (is_array($arr)) {
            foreach ($arr as $k => $v) {
                $keys_value[$k] = $v[$key];
            }

            if ($sort == 'DESC') {
                arsort($keys_value);
            } else {
                rsort($keys_value);
            }
            reset($keys_value);

            foreach ($keys_value as $k => $v) {
                $new_array[$k] = $arr[$k];
            }
        }

        return $new_array;
    }

    /**
     * Method  JsonStingToArray
     * 将数组中的string型的json数据转为数组
     * @author guangling1<guangling1@staff.weibo.com>
     * @static
     *
     * @param array $arr
     *
     * @return array
     */
    static function JsonStingToArray(array $arr) {
        foreach ($arr as $k => $v) {
            if (is_array($v)) {
                $arr[$k] = static::JsonStingToArray($v);
            } elseif (is_string($v) && static::is_json($v)) {
                $arr[$k] = json_decode($v,true);
            }
        }

        return $arr;
    }


    /**
     * Method  SplitKeyValue
     * 将key:value结构数组转为 ["$key"=>key,"$value"=>value] 结构
     * @author guangling1<guangling1@staff.weibo.com>
     * @static
     *
     * @param array  $arr
     * @param string $key
     * @param string $value
     *
     * @return array
     */
    static function splitKeyValue(array $arr,$key = 'name',$value = 'value') {
        $res = array();
        foreach($arr as $k => $v){
            $temp_arr = array();
            if(is_array($v)){
                $temp_arr[$key] = $k;
                $temp_arr[$value] = static::splitKeyValue($v,$key,$value);
                $res[] = $temp_arr;
            }else{
                if(is_int($k) && is_string($v)){
                    $temp_arr[$value] = $k;
                    $temp_arr[$key] = $v;
                }else{
                    $temp_arr[$key] = $k;
                    $temp_arr[$value] = $v;
                }
                $res[] = $temp_arr;
            }
        }
        return $res;
    }

    /**
     * Method  getArrayValueDeep
     * 从多维数组提取kv键值对
     * @author guangling1<guangling1@staff.weibo.com>
     * @static
     *
     * @param array $arr
     *
     * @return array
     */
    static function getArrayValueDeep(array $arr){
        $ret = [];

        foreach($arr as $k => $v){
            if(is_array($v)){
                $ret = $ret + self::getArrayValueDeep($v);
            }else{
                $ret[$k] = $v;
            }
        }

        return $ret;
    }

    /**
     * Method  is_assoc
     * 判断数组是否为关联数组
     * @author guangling1<guangling1@staff.weibo.com>
     * @static
     *
     * @param $arr
     *
     * @return bool
     */
    static function is_assoc($arr){
        return is_array($arr) && (array_keys($arr) !== range(0, count($arr) - 1) || (bool)count(array_filter(array_keys($arr), 'is_string')));
    }

    /**
     * param
     * 格式化参数
     *
     * @author haicheng
     * @return null|string
     *
     * @param        $val
     * @param null   $is_not_exit
     * @param string $type
     */
    static function param($val, $is_not_exit = null, $type = 'STRING') {
        $val = trim($val);
        switch ($type) {
            case 'INT':
                $val = $val + 0;
                $val = (empty($val) && ($val !== 0)) ? $is_not_exit : $val;
                break;
            case 'STRING':
                $val = ( string )$val;
                $val = empty($val) ? $is_not_exit : $val;
                break;
        }

        return $val;
    }


    public static function display_ui()
    {
        $loadavg = sys_getloadavg();
        foreach ($loadavg as $k=>$v)
        {
            $loadavg[$k] = round($v, 2);
        }
        $display_str = "\033[1A\n\033[K-----------------------------\033[47;30m PHPSPIDER \033[0m-----------------------------\n\033[0m";
        //$display_str = "-----------------------------\033[47;30m PHPSPIDER \033[0m-----------------------------\n\033[0m";
        $run_time_str = date('Y-m-d H:i', time());
        $display_str .= 'PHPSpider version:' . '5.6' . "          PHP version:" . PHP_VERSION . "\n";
        $display_str .= 'start time:'. date('Y-m-d H:i:s', time()).'   run ' . $run_time_str . " \n";

        $display_str .= 'spider name: ceshi' . "\n";
        $display_str .= 'task number: ' . "\n";
        $display_str .= 'load average: ' . implode(", ", $loadavg) . "\n";
        $display_str .= "document: https://doc.phpspider.org\n";

        $display_str .= self::display_task_ui();
//
//        if (self::$multiserver)
//        {
//            $display_str .= $this->display_server_ui();
//        }
//
//        $display_str .= $this->display_collect_ui();

        // 清屏
        //$this->clear_echo();
        // 返回到第一行,第一列
        //echo "\033[0;0H";
        $display_str .= "---------------------------------------------------------------------\n";
        $display_str .= "Press Ctrl-C to quit. Start success.";
//        if (self::$terminate)
        //        {
        //            $display_str .= "\n\033[33mWait for the process exits...\033[0m";
        //        }
        //echo $display_str;
        self::replace_echo($display_str);
    }

    public static function replace_echo($message, $force_clear_lines = NULL)
    {
        static $last_lines = 0;

        if(!is_null($force_clear_lines))
        {
            $last_lines = $force_clear_lines;
        }

        // 获取终端宽度
        $toss = $status = null;
        $term_width = exec('tput cols', $toss, $status);
        if($status || empty($term_width))
        {
            $term_width = 64; // Arbitrary fall-back term width.
        }

        $line_count = 0;
        foreach(explode("\n", $message) as $line)
        {
            $line_count += count(str_split($line, $term_width));
        }

        // Erasure MAGIC: Clear as many lines as the last output had.
        for($i = 0; $i < $last_lines; $i++)
        {
            // Return to the beginning of the line
            echo "\r";
            // Erase to the end of the line
            echo "\033[K";
            // Move cursor Up a line
            echo "\033[1A";
            // Return to the beginning of the line
            echo "\r";
            // Erase to the end of the line
            echo "\033[K";
            // Return to the beginning of the line
            echo "\r";
            // Can be consolodated into
            // echo "\r\033[K\033[1A\r\033[K\r";
        }

        $last_lines = $line_count;

        echo $message."\n";
    }


    public static function display_task_ui()
    {

        $display_str = "-------------------------------\033[47;30m TASKS \033[0m-------------------------------\n";

        $display_str .= "\033[47;30mtaskid\033[0m". str_pad('', 1+2-strlen('taskid')).
            "\033[47;30mtaskpid\033[0m". str_pad('', 1+2-strlen('taskpid')).
            "\033[47;30mmem\033[0m". str_pad('', 1+2-strlen('mem')).
            "\033[47;30mcollect succ\033[0m". str_pad('', 1-strlen('collect succ')).
            "\033[47;30mcollect fail\033[0m". str_pad('', 1-strlen('collect fail')).
            "\033[47;30mspeed\033[0m". str_pad('', 1+2-strlen('speed')).
            "\n";

        // "\033[32;40m [OK] \033[0m"
//        $task_status = $this->get_task_status_list(self::$serverid, self::$tasknum);
        $task_status[] = [
            'id' => '',
            'pid' => '',
            'mem' => '',
            'collect_succ' => '',
            'collect_fail' => '',
            'speed' => '',
        ];
        foreach ($task_status as $task)
        {
            //$task = json_decode($json, true);
            if (empty($task))
            {
                continue;
            }
            $display_str .= str_pad($task['id'], 10).
                str_pad($task['pid'], 10).
                str_pad($task['mem']."MB", 12).
                str_pad($task['collect_succ'], 4).
                str_pad($task['collect_fail'], 4).
                str_pad($task['speed']."/s", 4+2).
                "\n";
        }
        //echo "\033[9;0H";
        return $display_str;
    }

    public function clear_echo()
    {
        $arr = array(27, 91, 72, 27, 91, 50, 74);
        foreach ($arr as $a)
        {
            print chr($a);
        }
        //array_map(create_function('$a', 'print chr($a);'), array(27, 91, 72, 27, 91, 50, 74));
    }

    static function microtimeFloat()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }

    static function logSql($sql){

        Log::sql($sql);
    }
}