<?php

namespace app\models;

use app\library\Model;

class PostListData extends Model {
	
	public static function tableName() {
		
		return 'post_list_data';
	}
	
	public static function getList($condition = [], $page = 1, $rows = 20, $sort = 'id', $order = 'desc') {
		
		$model = PostListData::find();
		
		$offset = ($page - 1) * $rows;
		
		if(!empty($condition)) {
			$model->where($condition);
		}
		
		$rows = $model->offset($offset)->limit($rows)->orderBy($sort . ' ' . $order)->asArray()->all();
		
		return $rows;
	}
	
	public function getListCount($condition = []) {
		
		$model = PostListData::find();
		
		if(!empty($condition)) {
			$model->where($condition);
		}
		
		return $model->count();
	}
	
	public static function buildInfo($data = []) {
		$house_info = [
			'datetime'   => '',
			'url'        => '',
			'area'       => '',
			'title'      => '',
			'address'    => '',
			'flood'      => '',
			'tag'        => '',
			'price_info' => '',
			'price'      => '',
		];
		$data       = array_merge($house_info, $data);
		
		$model             = new PostListData();
		$model->datetime   = $data['datetime'];
		$model->url        = $data['url'];
		$model->area       = $data['area'];
		$model->title      = $data['title'];
		$model->address    = $data['address'];
		$model->flood      = $data['flood'];
		$model->tag        = $data['tag'];
		$model->price_info = $data['price_info'];
		$model->price      = $data['price'];
		
		return $model->insert();
		
	}
	
}
