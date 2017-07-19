<?php
namespace app\library;

use Yii;

/**
 * Class     ObjectApi
 * Api
 *
 * @author   haicheng
 */
class Api {

    private static $_tauth2_token = null;

    private static $_app_key = '4009338982';

    private static $_default_uid = '2608812381';

    /**
     * 导入标签
     *
     * @param        $display_name
     * @param        $app_id
     * @param        $customer_id
     * @param string $deep_link
     * @param string $url_h5
     * @param string $rule
     *
     * @return array|bool|mixed
     */
    public static function importTagObject ($display_name, $app_id, $customer_id, $deep_link = '', $url_h5 = '', $rule = '') {

        $api_down_url = sprintf(Yii::$app->params['api_down_template'], $app_id, $customer_id, $rule);
        $object_info  = [
            'display_name' => $display_name,
            'container_id' => $app_id,
            'py_id'        => $customer_id,
            'deep_link'    => $deep_link,
            'url_h5'       => $url_h5,
        ];

        $post_data = [
            'signature' => Yii::$app->params['signature'],
            'url'       => $api_down_url,
            'object'    => json_encode($object_info)
        ];

        $url         = Yii::$app->params['object_import_url'];
        $result      = '';
        $retry_count = 5;
        while (($retry_count--) > 0) {
            $result = Curl::post($url, $post_data);
            if (Curl::getHttpCode() === 200) {
                break;
            }
        }
        Log::info($url);
        Log::info($result);
        Log::info($post_data);

        $data = json_decode($result, true);
        if (!is_array($data)) {

            Log::error($result);

            return false;
        }

        if (isset($data['error_code']) && isset($data['error'])) {

            Log::error($result);

            return false;
        }

        return $data;
    }

    public static function getObjectInfoByObjectId ($object_id) {

        $template_url = Yii::$app->params['object_show_url'];
        $retry_count  = 5;

        $result = '';
        $url    = sprintf($template_url, Yii::$app->params['app_key'], $object_id);
        while (($retry_count--) > 0) {
            $result = Curl::get($url);
            if (Curl::getHttpCode() === 200) {
                break;
            }
        }
        Log::info($url);
        Log::info($result);

        $data = json_decode($result, true);
        if (!is_array($data)) {
            return false;
        }

        //验证是否有错误码和错误信息
        if (isset($data['error_code']) && isset($data['error'])) {
            Log::error($result);

            return false;
        }

        if (!isset($data['object'])) {
            Log::error($result);

            return false;
        }

        return $data['object'];
    }


    /**
     * build buildTagsFromMedia
     *
     * @param $customer_id
     * @param $photo_tag
     *
     * @return bool|mixed
     */
    public static function buildTagsFromMedia ($customer_id, $photo_tag) {

        if (empty($customer_id)) {
            return false;
        }

        $post_data = [
            'cuid'      => $customer_id,
            'photo_tag' => json_encode($photo_tag),
        ];

        $retry_count = 5;
        $url         = Yii::$app->params['media_tag_build_url'];
        while (($retry_count--) > 0) {
            $result = Curl::get($url, $post_data);
            if (Curl::getHttpCode() === 200) {
                break;
            }
        }

        Log::info($url);
        Log::info($post_data);

        $data = json_decode($result, true);

        if (!is_array($data)) {
            Log::error($result);

            return false;
        }

        if (isset($data['error_code']) && isset($data['error'])) {
            Log::error($result);

            return false;
        }

        return $data['data'];
    }


    public static function rebuildTagsFromMedia ($customer_id, $pid, $tag_info) {

        if (empty($customer_id)) {
            return false;
        }

        $post_data = [
            'pid'      => $pid,
            'uid'      => $customer_id,
            'tag_info' => $tag_info,
        ];

        $retry_count = 5;
        $url         = Yii::$app->params['media_tag_rebuild_url'];
        while (($retry_count--) > 0) {
            $result = Curl::get($url, $post_data);
            if (Curl::getHttpCode() === 200) {
                break;
            }
        }
        Log::info($url);
        Log::info($result);
        Log::info($post_data);

        $data = json_decode($result, true);

        if (!is_array($data)) {
            Log::error($result);

            return false;
        }

        if (isset($data['code']) && (100000 !== intval($data['code']))) {
            Log::error($result);

            return false;
        }

        return $data['data'];
    }

    /**
     * uploadPic
     * 图片上传
     *
     * @param $pic_url
     *
     * @return bool|mixed
     */
    public static function uploadPic ($pic_url) {

        if (empty($pic_url)) {
            Log::error( __METHOD__ . 'bad params');

            return false;
        }

        $token = self::_getTAuth2Token();

        //验证token
        if (false === $token) {
            Log::error( __METHOD__ . 'bad token');

            return false;
        }

        $http_header = array(self::_getTAuth2Authorization(self::$_default_uid, $token));

        $stream  = stream_context_create(array(
            'http' => array(
                'timeout' => 30
            )
        ));
        $content = @file_get_contents($pic_url, false, $stream);
        if (empty($content)) {
            Log::error( __METHOD__ . 'file_get_contents failure');

            return false;
        }
        $tmp_pic_name = tempnam("/tmp/", "tmp_file_");
        @file_put_contents($tmp_pic_name, $content);
        unset($stream);

        $post_data = [
            'source'     => Yii::$app->params['app_key'],
            'pic'        => $tmp_pic_name,
            'print_mark' => 0,
            'ori'        => 1
        ];

        $retry_count = 5;
        $url         = Yii::$app->params['upload_pic'];
        while (($retry_count--) > 0) {
            $result = Curl::post($url, $post_data, $http_header, null, $tmp_pic_name, 'pic');
            if (Curl::getHttpCode() === 200) {
                break;
            }
        }
        @unlink($tmp_pic_name);

        Log::info($url);
        Log::info($post_data);

        $data = json_decode($result, true);

        if (!is_array($data)) {
            Log::error($result);

            return false;
        }

        if (isset($data['error_code']) && isset($data['error'])) {
            Log::error($result);

            return false;
        }

        return $data;
    }

    /**
     * getBatchByPid
     *
     * @param null $pids_mids
     *
     * @return bool|mixed
     */
    public static function getBatchByPid ($pids_mids = null) {

        if (empty($pids_mids)) {
            return false;
        }

        $retry_count  = 5;
        $url_template = Yii::$app->params['get_batch_by_pid'];
        $url          = sprintf($url_template, $pids_mids);

        while (($retry_count--) > 0) {
            $result = Curl::get($url);
            if (Curl::getHttpCode() === 200) {
                break;
            }
        }

        Log::info($url);
        Log::info($result);

        $data = json_decode($result, true);

        if (!is_array($data)) {
            Log::error($result);

            return false;
        }

        if (isset($data['code']) && (100000 !== intval($data['code']))) {
            Log::error($result);

            return false;
        }

        return $data;
    }

    /**
     * deletePicTag
     *
     * @param null $pid
     *
     * @return bool|mixed
     */
    public static function deletePicTag ($pid = null) {

        if (empty($pid)) {
            return false;
        }

        $retry_count  = 5;
        $url_template = Yii::$app->params['delete_pic_tag'];
        $url          = sprintf($url_template, $pid);

        while (($retry_count--) > 0) {
            $result = Curl::get($url);
            if (Curl::getHttpCode() === 200) {
                break;
            }
        }

        Log::info($url);
        Log::info($result);

        $data = json_decode($result, true);

        if (!is_array($data)) {
            Log::error($result);

            return false;
        }

        if (isset($data['code']) && (100000 !== intval($data['code']))) {
            Log::error($result);

            return false;
        }

        return $data;
    }

    /**
     * registerDeeplink
     *
     * @param $oid
     * @param $uid
     * @param $appid
     * @param $deeplink
     * @param $url_h5
     * @param $app_type
     *
     * @return bool
     */
    public static function registerDeeplink($oid, $uid, $appid, $deeplink, $url_h5, $app_type) {

        $template_url = Yii::$app->params['deeplink_register'];
        //ios=1；android=0
        $appid        = ($app_type == 1) ? 'i' . $appid : $appid;
        $source       = md5(md5( 'oid' . $oid . 'uid' . $uid) . 'jdekufn#293kdl2@91ld');

        $url = sprintf($template_url, $oid, $uid, $appid, urlencode($deeplink), $source, $url_h5);
        $retry_count = 3;

        $result = '';
        while (($retry_count--) > 0) {
            $result = Curl::get($url);
            if (Curl::getHttpCode() === 200) {
                break;
            }
        }

        Log::info($url);
        Log::info($result);

        $data = json_decode($result, true);

        if (!is_array($data)) {
            Log::error($result);

            return false;
        }

        if (isset($data['code']) && (100000 !== intval($data['code']))) {
            Log::error($result);

            return false;
        }

        return true;
    }

    private static function _getTAuth2Token () {
        if (!empty(self::$_tauth2_token)) {
            return self::$_tauth2_token;
        }

        $url_template = 'http://adinfapi.biz.weibo.com/token/get?source=%s&is_update=1';

        $url = sprintf($url_template, self::$_app_key);

        $retry_count = 5;

        $result = '';

        while (($retry_count--) > 0) {
            $result = Curl::get($url);
            if (Curl::getHttpCode() === 200) {
                break;
            }
        }

        $data = json_decode($result, true);
        if (!is_array($data)) {

            return false;
        }

        if (isset($data['ret']) && 1 == $data['ret']) {

            return false;
        }

        if (!isset($data['data']['tauth_token']) || !isset($data['data']['tauth_token_secret'])) {

            return false;
        }

        self::$_tauth2_token = $data['data'];

        return self::$_tauth2_token;
    }

    private static function _getTAuth2Authorization ($uid, $token) {
        if (!empty($token)) {
            $tokenStr = urlencode($token['tauth_token']);
            $param    = 'uid=' . $uid;
            $sign     = urlencode(base64_encode(hash_hmac('sha1', $param, $token['tauth_token_secret'], true)));
            $auth     = 'Authorization: TAuth2 token="' . $tokenStr . '", param="' . $param . '", sign="' . $sign . '"';
        } else {
            $auth = '';
        }

        return $auth;
    }
}