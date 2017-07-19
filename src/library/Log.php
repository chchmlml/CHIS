<?php
namespace app\library;

/**
 * Class     Log
 * 日志文件类
 *
 * @author   haicheng
 */
class Log {

    /**
     * Variable  _file_template
     * 文件模板
     *
     * @author   haicheng
     * @static
     * @var      null
     */
    private static $_file_template = null;

    /**
     * Variable  _content_template
     * 内容模板
     *
     * @author   haicheng
     * @static
     * @var      null
     */
    private static $_content_template = null;

    /**
     * Variable  _default_content_template
     * 默认内容模板
     *
     * @author   haicheng
     * @static
     * @var      string
     */
    private static $_default_content_template = "{date}(Y-m-d H:i:s) {content} [{file} at line{line}]\n";

    /**
     * Variable  _module_name
     * 模块名称
     *
     * @author   haicheng
     * @static
     * @var      null
     */
    private static $_module_name = null;

    /**
     * Variable  _default_module_name
     * 默认模块名称
     *
     * @author   haicheng
     * @static
     * @var      string
     */
    private static $_default_module_name = 'default';

    /**
     * Variable  _type_list
     * 类型列表
     *
     * @author   haicheng
     * @static
     * @var      array
     */
    private static $_type_list = array(
        'trace',
        'debug',
        'info',
        'warning',
        'error',
        'message',
        'mail',
    );

    /**
     * Variable  _default_type
     * 默认类型
     *
     * @author   haicheng
     * @static
     * @var      string
     */
    private static $_default_type = 'default';

    /**
     * Method  setConfig
     * 设置配置
     *
     * @author haicheng
     * @static
     */
    public static function setConfig () {

        self::$_file_template    = getenv('log_path')  . date('Y-m-d', time()) . '.log';
        self::$_content_template = self::$_default_content_template;
        self::$_module_name      = self::$_default_module_name;
    }

    /**
     * Method  setFileTemplate
     * 设置文件模板
     *
     * @author haicheng
     * @static
     *
     * @param $file_template
     */
    public static function setFileTemplate ($file_template) {
        self::$_file_template = $file_template;
    }

    /**
     * Method  setContentemplate
     * 设置内容模板
     *
     * @author haicheng
     * @static
     *
     * @param $content_template
     */
    public static function setContentemplate ($content_template) {
        self::$_content_template = $content_template;
    }

    /**
     * Method  setModuleName
     * 设置模块名称
     *
     * @author haicheng
     * @static
     *
     * @param $module_name
     */
    public static function setModuleName ($module_name) {
        self::$_module_name = $module_name;
    }

    /**
     * _formatContent
     *
     * @author haihceng
     * @return string
     *
     * @param $content
     */
    private static function _formatContent ($content = '') {
        $content = is_array($content) ? var_export($content, true) : $content;

        return $content;
    }

    /**
     * Method  trace
     * 写入trace类型日志
     *
     * @author haicheng
     * @static
     *
     * @param $content
     */
    public static function trace ($content = '') {
        self::write('trace', self::_formatContent($content), true);
    }

    /**
     * Method  debug
     * 写入debug类型日志
     *
     * @author haicheng
     * @static
     *
     * @param $content
     */
    public static function debug ($content = '') {
        self::write('debug', self::_formatContent($content), true);
    }

    /**
     * Method  info
     * 写入info类型日志
     *
     * @author haicheng
     * @static
     *
     * @param $content
     */
    public static function info ($content = '') {
        self::write('info', self::_formatContent($content), true);
    }

    /**
     * Method  warning
     * 写入warning类型日志
     *
     * @author haicheng
     * @static
     *
     * @param $content
     */
    public static function warning ($content = '') {
        self::write('warning', self::_formatContent($content), true);
    }

    /**
     * Method  error
     * 写入error类型日志
     *
     * @author haicheng
     * @static
     *
     * @param $content
     */
    public static function error ($content = '') {
        self::write('error', self::_formatContent($content), true);
    }

    /**
     * Method  message
     * 写入message类型日志
     *
     * @author haicheng
     * @static
     *
     * @param $content
     */
    public static function message ($content = '') {
        self::write('message', self::_formatContent($content), true);
    }

    public static function sql($content = ''){

        //验证所需变量
        if (empty(self::$_file_template) || empty(self::$_content_template) || empty(self::$_module_name)) {
            self::setConfig();
        }

        $file = ROOT_PATH . '/runtime/logs/sql_' . date('Ymd', time()) . '.sql';;

        self::_writeToFile($file, $content);
    }

    /**
     * Method  write
     * 写入日志
     *
     * @author haicheng
     * @static
     *
     * @param string $type
     * @param string $content
     * @param bool   $is_self_call
     */
    public static function write ($type, $content, $is_self_call = true) {
        //验证所需变量
        if (empty(self::$_file_template) || empty(self::$_content_template) || empty(self::$_module_name)) {
            self::setConfig();
        }

        //过滤日志类型
        if (!in_array(strtolower($type), self::$_type_list)) {
            $type = self::$_default_type;
        }

        //获取back trace
        $backtrace_list = debug_backtrace();

        //验证是否为类内调用
        if (true === $is_self_call && isset($backtrace_list[1])) {
            //如果是类内调用, 取下标为1的元素
            $file = $backtrace_list[1]['file'];
            $line = $backtrace_list[1]['line'];
        } else {
            //如果非类内调用, 取下标为0的元素
            $file = $backtrace_list[0]['file'];
            $line = $backtrace_list[0]['line'];
        }

        $file_split = explode('/', $file);
        $file       = (count($file_split) >= 1) ? $file_split[count($file_split) - 1] : $file;
        //替换内容
        $search = array(
            '{content}',
            '{file}',
            '{line}'
        );

        $replace = array(
            $content,
            $file,
            $line
        );

        $content = self::_replaceTemplate($search, $replace, self::$_content_template);

        //替换文件
        $search = array(
            '{module}',
            '{type}'
        );

        $replace = array(
            self::$_module_name,
            $type
        );

        $file = self::_replaceTemplate($search, $replace, self::$_file_template);

        self::_writeToFile($file, $content);
    }

    /**
     * Method  _replaceTemplate
     * 解析模板
     *
     * @author   haicheng
     * @static
     *
     * @param $search
     * @param $replace
     * @param $template
     *
     * @return mixed
     * @internal param $type
     */
    private static function _replaceTemplate ($search, $replace, $template) {
        $template = preg_replace_callback('/{date}\((.*)\)/', function ($matches) {
            $date_format = isset($matches[1]) ? $matches[1] : 'Y-m-d H:i:s';

            return date($date_format);
        }, $template);

        return str_replace($search, $replace, $template);
    }

    /**
     * Method  _writeToFile
     * 写入文件
     *
     * @author haicheng
     * @static
     *
     * @param string $file
     * @param string $content
     * @param string $mode
     *
     * @return bool
     */
    private static function _writeToFile ($file, $content, $mode = 'a') {
        $handle = fopen($file, $mode);

        if (false === $handle) {
            return false;
        }

        $is_locked = flock($handle, LOCK_EX);

        $micro_start_time = microtime(true);

        do {
            if (false === $is_locked) {
                usleep(round(rand(0, 100) * 100));
            }
        } while (false === $is_locked && (microtime(true) - $micro_start_time) < 1000);

        if (true === $is_locked) {
            fwrite($handle, $content);

            flock($handle, LOCK_UN);
        }

        fclose($handle);
        unset($handle);

        @chmod($file, 0777);
        return true;
    }
}