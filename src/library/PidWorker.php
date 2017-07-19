<?php

namespace app\library;

use app\services\Command;

/**
 * PidWorker
 *
 * @author   haicheng
 * @datetime 17/3/31 上午10:46
 */

class PidWorker {

    private $_name = '';

    public function __construct ($name = '') {

        $this->_name = $name;
    }

    public function run () {

        //echo "worker {$this->_name} is running... \n";
        $command_service = new Command();
        return $command_service->buildPid();
        //        if (!$command_service->buildPid()->isError()) {
        //
        //            throw new ApiException($command_service->getError());
        //        }
    }
}
