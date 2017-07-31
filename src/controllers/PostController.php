<?php

namespace app\controllers;

use app\library\Controller;

/**
 * 报表
 *
 * @author   haicheng
 */
class PostController extends Controller {
	
	public $enableCsrfValidation = false;
	
	public function actionIndex() {
		
		echo 'hello world';
		exit;
	}
	
	public function actionLianjia() {
		
		$cities = [
			"东城",
			"西城",
			"朝阳",
			"海淀",
			"丰台",
			"石景山",
			"通州",
			"昌平",
			"大兴",
			"亦庄开发区",
			"顺义",
			"房山",
			"门头沟",
			"平谷",
			"怀柔",
			"密云",
			"延庆",
		];
		
		$post_data = [];
		foreach($cities as $c) {
			$post_data[] = [
				'name'     => $c,
				'type'     => 'line',
				'stack'    => '总量',
				'areaStye' => '{normal=> {}}',
				'showSymbol' => false,
				'data'     => [
					120,
					132,
					101,
					134,
					90,
					230,
					210
				]
			];
		}
		
		$post_date = ['周一','周二','周三','周四','周五','周六','周日'];
		
		return $this->getView()->render('/post/lianjia', [
			'cities' => json_encode($cities),
			'post_data' => json_encode($post_data),
			'post_date' => json_encode($post_date)
		]);
	}
	
}
