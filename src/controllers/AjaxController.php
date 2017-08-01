<?php

namespace app\controllers;

use app\models\PostListData;
use \Yii;
use app\library\Controller;

/**
 * ajax报表
 *
 * @author   haicheng
 */
class AjaxController extends Controller {
	
	public $enableCsrfValidation = false;
	
	public function actionIndex() {
		
		echo 'hello world';
		exit;
	}
	
	public function actionData() {
		
		$page     = Yii::$app->request->get('page', 1);
		$rows     = Yii::$app->request->get('rows', 20);
		$sort     = Yii::$app->request->get('sort', 'id');
		$order    = Yii::$app->request->get('order', 'DESC');
		$datetime = Yii::$app->request->get('datetime', '');
		$area     = Yii::$app->request->get('area', '');
		
		$condition = [];
		if(!empty($datetime)) {
			$condition['datetime'] = $datetime . ' 00:00:00';
		}
		if(!empty($area)) {
			$condition['area'] = $area;
		}
		
		$data_model = new PostListData();
		$result_of_count = $data_model->getListCount($condition);
		$result_of_query = $data_model->getList($condition, $page , $rows , $sort , $order);
		
		$this->dataJson([
			'total' => $result_of_count,
			'rows'  => $result_of_query
		]);
	}
	
}
