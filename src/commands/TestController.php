<?php

namespace app\commands;

use app\library\ControllerCommand;
use app\library\Log;

/**
 * task
 *
 * @author   haicheng
 */
class TestController extends ControllerCommand {

    public function actionIndex() {

    	echo ' everything is ok';
        Log::info(__METHOD__ . ' everything is ok');
    }
}
