<?php

namespace app\services;

use app\library\Log;
use Yii;
use app\library\Api;
use app\library\Service;
use yii\base\Exception;
use yii\db\Connection;

class Command extends Service {

    private $_result_of_data = [];

    public function __construct () {
        parent::__construct();

        return $this;
    }

    public function prepareUrlForUpdate () {

        $result_of_queue = Yii::$app->params['db_bp_connect']->createCommand('SELECT * FROM url_pid_mapping_table WHERE status=:status LIMIT 0,100')
            ->bindValue(':status', 0)->queryAll();
        if (!empty($result_of_queue)) {
            $data_of_insert = [];
            foreach ($result_of_queue as $val) {
                $result_of_one = Yii::$app->params['db_bp_connect']->createCommand('SELECT * FROM queue_of_mapping_ids WHERE mapping_id=:mapping_id LIMIT 0,1')
                    ->bindValue(':mapping_id', $val['id'])->queryOne();
                if (empty($result_of_one)) {
                    $data_of_insert[] = [
                        $val['id'],
                        $val['picture_url']
                    ];
                }
            }
            if (!empty($data_of_insert)) {
                Yii::$app->params['db_bp_connect']->createCommand()->batchInsert('queue_of_mapping_ids', [
                    'mapping_id',
                    'picture_url'
                ], $data_of_insert)->execute();
                Log::info(__METHOD__ . ' update queue count ' . count($data_of_insert));
            }

            unset($data_of_insert);
        } else {
            Log::info(__METHOD__ . '  sleep');
            sleep(1);
        }

        return $this;
    }

    public function buildPid () {

        try {

            Yii::$app->params['db_bp_connect']->createCommand('SET AUTOCOMMIT=0')->execute();
            Yii::$app->params['db_bp_connect']->createCommand('BEGIN WORK')->execute();

            $result_of_one = Yii::$app->params['db_bp_connect']->createCommand('SELECT * FROM queue_of_mapping_ids LIMIT 0,1 FOR UPDATE')
                ->queryOne();

            if (!empty($result_of_one)) {
                if (!empty($result_of_one['picture_url'])) {

                    $result_of_upload = Api::uploadPic($result_of_one['picture_url']);
                    if (false === $result_of_upload) {

                        Log::info(__METHOD__ . ' 图片上传失败');
                        $this->setError('图片上传失败');
                    } else {
                        $result_of_update = Yii::$app->params['db_settle_connect']->createCommand('UPDATE url_pid_mapping_table SET status = :status,picture_id = :picture_id WHERE id=:id ')
                            ->bindValue(':id', $result_of_one['mapping_id'])->bindValue(':status', 1)
                            ->bindValue(':picture_id', $result_of_upload['pic_id'])->execute();
                        if (!$result_of_update) {

                            Log::info(__METHOD__ . ' 图片pid更新失败');
                            $this->setError('图片pid更新失败');
                        } else {

                            Log::info(__METHOD__ . ' 图片pid ' . json_encode($result_of_upload));
                        }
                        $result_of_delete = Yii::$app->params['db_bp_connect']->createCommand()
                            ->delete('queue_of_mapping_ids', 'id = ' . $result_of_one['id'])->execute();
                        if (!$result_of_delete) {

                            Log::info(__METHOD__ . ' 清除队列失败 id ' . $result_of_one['id']);
                            $this->setError('清除队列失败');
                        } else {

                            Log::info(__METHOD__ . ' 清除队列 ' . json_encode($result_of_one['id']));
                        }
                    }
                }
            } else {
                return false;
            }
        } catch (Exception $e) {

            Log::info(__METHOD__ . ' 更新失败了:' . $e->getMessage());
            $this->setError('更新失败了:' . $e->getMessage());

            return false;
        } finally {
            Yii::$app->params['db_bp_connect']->createCommand('COMMIT WORK')->execute();
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getResultOfData () {
        return $this->_result_of_data;
    }

    public function mock () {

        $imgs  = [
            "http://wx1.sinaimg.cn/large/006CMp2vgy1fcgqkgcar9j30yi0yijtu.jpg",
            "http://wx2.sinaimg.cn/large/006CMp2vgy1fcgqkl1td8j30yi0yiwgy.jpg",
            "http://wx2.sinaimg.cn/large/006CMp2vgy1fcgqkpt3vtj30yi0yiq56.jpg",
            "http://wx2.sinaimg.cn/large/006CMp2vgy1fcgqkt12ctj30yi0yitb5.jpg",
            "http://wx2.sinaimg.cn/large/006CMp2vgy1fcgqkx4krej30yi0yijtk.jpg",
            "http://wx1.sinaimg.cn/large/006CMp2vgy1fcgql2axudj30yi0yiach.jpg",
            "http://wx2.sinaimg.cn/large/006CMp2vgy1fcgql4ar66j30yi0yi40x.jpg",
            "http://wx1.sinaimg.cn/large/006CMp2vgy1fcgql9kv3fj30yi0yigo1.jpg",
            "http://wx2.sinaimg.cn/large/006CMp2vgy1fcgqlfbi4xj30yi0yigo3.jpg"
        ];
        $count = 1000;
        while ($count-- > 0) {
            Yii::$app->params['db_settle_connect']->createCommand()->batchInsert('queue_of_mapping_ids', [
                'picture_url'
            ], $imgs[rand(0,8)])->execute();
        }

    }

}
