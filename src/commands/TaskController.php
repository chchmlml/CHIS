<?php

namespace app\commands;

use app\library\ControllerCommand;
use app\library\Log;
use app\services\LianjiaCommand;

/**
 * task
 *
 * @author   haicheng
 */
class TaskController extends ControllerCommand {
	
	public function actionStart () {
		
		Log::info(__METHOD__ . ' task start');
		(new LianjiaCommand())->start();
		Log::info(__METHOD__ . ' everything is ok');
	}
}
