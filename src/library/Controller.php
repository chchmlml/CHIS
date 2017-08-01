<?php
/**
 * service
 *
 * @author   haicheng
 * @datetime 17/3/29 下午4:10
 */

namespace app\library;

class Controller extends \yii\web\Controller {


    public function success($data){
        $this->parseJson($data);
    }

    public function parseJson($data){
        $this->asJson([
            'code'    => 0,
            'message' => 'success',
            'result'  => $data,
        ])->send();
        exit;
    }
	
	public function dataJson(array $data = array()) {
		
		header('Content-type: application/json;charset=UTF-8');
		echo json_encode($data, JSON_UNESCAPED_UNICODE);
		exit();
	}
}