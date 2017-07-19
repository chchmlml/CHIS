<?php

namespace app\library;

use Yii;

/**
 * Class     Curl
 * CURL类
 *
 * @author   haicheng
 */
class Curl {

    /**
     * Variable  _connect_timeout
     *
     * @author   haicheng
     * @static
     * @var      int
     */
    private static $_connect_timeout = 15;

    /**
     * Variable  _timeout
     *
     * @author   haicheng
     * @static
     * @var      int
     */
    private static $_timeout = 15;

    /**
     * Variable  _http_code
     *
     * @author   haicheng
     * @static
     * @var
     */
    private static $_http_code;

    /**
     * Variable  _http_info
     *
     * @author   haicheng
     * @static
     * @var
     */
    private static $_http_info;

    /**
     * Variable  _error_code
     *
     * @author   haicheng
     * @static
     * @var
     */
    private static $_error_code;

    /**
     * Variable  _error_info
     *
     * @author   haicheng
     * @static
     * @var
     */
    private static $_error_info;

    /**
     * Variable  _request_url
     *
     * @author   haicheng
     * @static
     * @var
     */
    private static $_request_url;

    /**
     * Variable  _request_data
     *
     * @author   haicheng
     * @static
     * @var      null
     */
    private static $_request_data = null;

    /**
     * Method  get
     * 发送get请求
     *
     * @author haicheng
     * @static
     *
     * @param      $url
     * @param null $data
     * @param null $header
     * @param null $userpwd
     *
     * @return string
     */
    public static function get($url, $data = null, $header = null, $userpwd = null) {
        return self::_sendHttpRequest('GET', $url, $data, $header, $userpwd);
    }

    /**
     * Method  post
     * 发送post请求
     *
     * @author haicheng
     * @static
     *
     * @param      $url
     * @param null $data
     * @param null $header
     * @param null $userpwd
     * @param null $file_path
     * @param null $file_key
     *
     * @return string
     */
    public static function post($url, $data = null, $header = null, $userpwd = null, $file_path = null, $file_key = null) {
        return self::_sendHttpRequest('POST', $url, $data, $header, $userpwd, $file_path, $file_key);
    }

    /**
     * Method  _sendHttpRequest
     * 发送http请求
     *
     * @author haicheng
     * @static
     *
     * @param       $method
     * @param       $url
     * @param null  $data
     * @param array $header
     * @param null  $userpwd
     * @param null  $file_path
     * @param null  $file_key
     *
     * @return mixed
     */
    private static function _sendHttpRequest($method, $url, $data = null, $header = array(), $userpwd = null, $file_path = null, $file_key = null) {


        $curl = curl_init();

        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, self::$_connect_timeout);
        curl_setopt($curl, CURLOPT_TIMEOUT, self::$_timeout);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_ENCODING, '');
        curl_setopt($curl, CURLOPT_HEADER, false);

        $method = strtoupper($method);
        if ('GET' === $method) {
            if ($data !== null) {
                if (strpos($url, '?')) {
                    $url .= '&';
                } else {
                    $url .= '?';
                }
                $url .= http_build_query($data);
            }

        } elseif ('POST' === $method) {
            curl_setopt($curl, CURLOPT_POST, true);
            if (null !== $file_path && null !== $file_key) {
                $data[$file_key] = new \CURLFile($file_path);
                if (!empty($data)) {
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                }
            } else {
                if (!empty($data)) {
                    if (is_string($data)) {
                        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                    } else {
                        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
                    }
                }
            }

        }

        if (null !== $userpwd) {
            curl_setopt($curl, CURLOPT_USERPWD, $userpwd);
        }

        if (null !== $header) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        }

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLINFO_HEADER_OUT, true);

        $response = curl_exec($curl);

        self::$_http_code    = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        self::$_http_info    = curl_getinfo($curl);
        self::$_error_code   = curl_errno($curl);
        self::$_error_info   = curl_error($curl);
        self::$_request_url  = $url;
        self::$_request_data = $data;

        curl_close($curl);

        return $response;
    }

    /**
     * Method  getHttpCode
     * 获取http状态码
     *
     * @author haicheng
     * @static
     * @return int
     */
    public static function getHttpCode() {
        return self::$_http_code;
    }

    /**
     * Method  getHttpInfo
     * 获取http信息
     *
     * @author haicheng
     * @static
     * @return string
     */
    public static function getHttpInfo() {
        return self::$_http_info;
    }

    /**
     * Method  getErrorCode
     * 获取错误码
     *
     * @author haicheng
     * @static
     * @return int
     */
    public static function getErrorCode() {
        return self::$_error_code;
    }

    /**
     * Method  getErrorInfo
     * 获取错误信息
     *
     * @author haicheng
     * @static
     * @return string
     */
    public static function getErrorInfo() {
        return self::$_error_info;
    }

    /**
     * Method  getRequestUrl
     * 获取请求URL
     *
     * @author haicheng
     * @static
     * @return string
     */
    public static function getRequestUrl() {
        return self::$_request_url;
    }

    /**
     * Method  getRequestData
     * 获取请求数据
     *
     * @author haicheng
     * @static
     * @return null
     */
    public static function getRequestData() {
        return self::$_request_data;
    }

}