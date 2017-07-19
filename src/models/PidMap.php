<?php

namespace app\models;

use app\library\Model;

class PidMap extends Model {

    public function getList () {

        $rows = $this->query->select(['*'])->from('url_pid_mapping_table')->limit(10)->all();

        return $rows;
    }


}
