<?php

namespace app\services;

use app\library\Api;
use app\library\Service;

class Tag extends Service {

    private $_customer_id = 0;

    private $_tag_objects = [];

    private $_result_of_data = [];

    /**
     * @return int
     */
    public function getCustomerId () {
        return $this->_customer_id;
    }

    /**
     * buildTagObjects
     *
     * @param $param_tags
     *
     * @return $this
     */
    public function buildTagObjects ($param_tags) {

        if (empty($param_tags)) {
            $this->setError('buildTagObjects set params error');

            return $this;
        }
        foreach ($param_tags as $tag) {
            $result_of_object = Api::importTagObject($tag['title'], $tag['app_id'], $tag['customer_id'], $tag['deep_link'], $tag['url_h5'], $tag['rule']);
            if (false === $result_of_object) {
                $this->setError('importTagObject failure');

                return $this;
            }

            if (empty($this->_customer_id)) {
                $this->_customer_id = $tag['customer_id'];
            }

            $result_of_current_object = Api::getObjectInfoByObjectId($result_of_object['object_id']);
            if (false === $result_of_current_object) {
                $this->setError('getObjectInfoByObjectId failure');

                return $this;
            }
            $this->_tag_objects[] = [
                'tag'        => $tag['title'],
                'tag_uid'    => $tag['customer_id'],
                'object_id'  => $result_of_object['object_id'],
                'short_url'  => $result_of_object['short_url'],
                'url'        => $result_of_current_object['target_url'],
                'mobile_url' => $result_of_current_object['target_url'],
            ];
        }

        return $this;
    }

    /**
     * setPidTag
     *
     * @param $param_pids
     *
     * @return $this
     */
    public function buildPidTag ($param_pids) {

        if (empty($param_pids)) {
            $this->setError('setPidTag set params error');

            return $this;
        }

        $param_pids_mids = null;

        $params_of_tag_info = [];
        foreach ($param_pids as $pid) {

            $tag_info = [];
            foreach ($this->_tag_objects as $objects) {
                $tag_info[] = [
                    'tag'           => $objects['tag'],
                    'tag_uid'       => $objects['tag_uid'],
                    'tag_type'      => 'app',
                    'tag_object_id' => $objects['object_id'],
                    'pos'           => [
                        'x' => 0.68,
                        'y' => 0.50
                    ],
                    'dir'           => '0',
                    'inherit'       => '',
                    'url'           => $objects['url'],
                    'mobile_url'    => $objects['mobile_url'],
                ];
            }

            $params_of_tag_info[$pid['pid']] = $tag_info;
            $param_pids_mids[]               = '"' . $pid['pid'] . '":"' . $pid['mid'] . '"';
        }

        $result_of_build_tag = Api::buildTagsFromMedia($this->_customer_id, $params_of_tag_info);
        if (false === $result_of_build_tag) {
            $this->setError('buildTagsFromMedia failure');

            return $this;
        }

        foreach ($result_of_build_tag as $pid => $tag) {
            //尝试删除tag pic绑定
            Api::deletePicTag($pid);

            $result_of_rebuild_tag = Api::rebuildTagsFromMedia($this->_customer_id, $pid, $tag);
            if (false === $result_of_rebuild_tag) {
                $this->setError('rebuildTagsFromMedia failure');

                return $this;
            }
        }

        $result_of_pidmid = Api::getBatchByPid($this->_getPidsMids($param_pids_mids));
        if (false === $result_of_pidmid) {
            $this->setError('getBatchByPid failure');

            return $this;
        }

        $this->_result_of_data = $result_of_pidmid['data'];

        return $this;
    }

    private function _getPidsMids ($param_pids_mids) {

        if (!is_array($param_pids_mids)) {

            return '';
        }

        return '{' . implode(',', $param_pids_mids) . '}';
    }

    /**
     * @return array
     */
    public function getResultOfData () {
        return $this->_result_of_data;
    }

}
