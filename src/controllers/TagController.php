<?php

namespace app\controllers;

use app\library\Controller;
use app\models\Customers;
use app\services\Tag;
use Yii;
use yii\web\NotFoundHttpException;
use app\library\ApiException;

/**
 * 标签创建
 *
 * @author   haicheng
 */
class TagController extends Controller {

    public $enableCsrfValidation = false;

    private $_params = [];

    public function actionIndex () {
        $this->success(getenv('bootstrap'));
        //return $this->render('@welcome');
    }

    public function actionTest () {
        $customer_model = new Customers();
        var_dump($customer_model->getUserList());
        exit;
    }

    /**
     * actionBuild
     * 创建
     *
     * @author: haicheng
     */
    public function actionBuild () {

        if(!Yii::$app->request->getIsPost()){

            throw new NotFoundHttpException('bad request');
        }

        $this->_params = Yii::$app->request->post();

        //校验参数
        if (!$this->_validateParams()) {

            Yii::error($this->_params, __METHOD__);
            throw new ApiException('bad params');
        }
        $tag_service = new Tag();

        //创建app tag对象
        if(!$tag_service->buildTagObjects($this->_params['tags'])->isError()){

            throw new ApiException('buildTagObjects failure');
        }

        //创建pid tag object关联
        $result_of_data = $tag_service->buildPidTag($this->_params['pids'])->isError();
        if(!$result_of_data){

            throw new ApiException('buildPidTag failure');
        }

        $this->success($tag_service->getResultOfData());
    }

    /**
     * _validateParams
     * 参数校验
     *
     * @author: haicheng
     * @return bool
     */
    private function _validateParams () {

        if (!isset($this->_params['tags']) || !isset($this->_params['pids'])) {

            return false;
        }

        $this->_params['tags'] = json_decode($this->_params['tags'], true);
        $tag_count = count($this->_params['tags']);
        if($tag_count > 5 || $tag_count < 1){

            return false;
        }
        foreach ($this->_params['tags'] as $tag) {
            if (
                !isset($tag['title']) || empty($tag['title']) ||
                !isset($tag['app_id']) || empty($tag['app_id']) ||
                !isset($tag['customer_id']) || empty($tag['customer_id']) ||
                !isset($tag['rule'])
            ) {

                return false;
            }
        }

        $this->_params['pids'] = json_decode($this->_params['pids'], true);
        $pids_count = count($this->_params['pids']);
        if($pids_count > 9 || $pids_count < 1){

            return false;
        }
        foreach ($this->_params['pids'] as $pid) {
            if (
                !isset($pid['pid']) || empty($pid['pid'])
            ) {

                return false;
            }
        }

        return true;
    }
}
