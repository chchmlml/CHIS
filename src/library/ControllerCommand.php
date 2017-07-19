<?php
/**
 * service
 *
 * @author   haicheng
 * @datetime 17/3/29 下午4:10
 */

namespace app\library;

class ControllerCommand extends \yii\console\Controller {


    public function success($data){
        $this->parseJson($data);
    }

    public function parseJson($data){

        header('Content-type: application/json; charset=utf-8');
        echo json_encode([
            'code'    => 0,
            'message' => 'success',
            'result'  => $data,
        ]);
        exit;
    }
}