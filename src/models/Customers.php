<?php

namespace app\models;

use app\library\Model;

class Customers extends Model {

    public function getUserList () {

        $rows = $this->query->select(['*'])->from('customers')->limit(10)->all();

        return $rows;
    }

}
