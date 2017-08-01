<?php
/**
 * model
 *
 * @author   haicheng
 * @datetime 17/3/29 下午4:10
 */

namespace app\library;

use yii\db\ActiveRecord;

class Model extends ActiveRecord {
	
	
	public function __construct(array $config = []) {
		parent::__construct($config);
	}
}