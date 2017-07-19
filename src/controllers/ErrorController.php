<?php

namespace app\controllers;

class ErrorController extends \yii\web\Controller
{
    public function actionHandle()
    {
        $exception = \Yii::$app->errorHandler->exception;
        $this->asJson([
            'code'    => -1,
            'message' => $exception->getMessage(),
            'result'  => '',
        ])->send();
    }

}
