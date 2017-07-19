<?php
/**
 * model
 *
 * @author   haicheng
 * @datetime 17/3/29 下午4:10
 */

namespace app\library;

use \Yii;
use \yii\base\Object;
use yii\db\Query;

class Model extends Object {

    /**
     * @var \yii\db\Connection
     */
    protected $db = null;

    /**
     * @var \yii\db\Query
     */
    protected $query = null;

    public function __construct (array $config = []) {
        parent::__construct($config);

        $this->db = Yii::$app->db;
        $this->query = new Query();
    }


}