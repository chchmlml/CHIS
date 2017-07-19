<?php

namespace app\library;

use app\services\Command;

/**
 * PidMaster
 *
 * @author   haicheng
 * @datetime 17/3/31 上午10:46
 */

class PidMaster {

    public function run () {

        $command = new Command();
        while (true) {
            sleep(1);
            //$command->prepareUrlForUpdate();
            //unset($command);
        }
    }
}
