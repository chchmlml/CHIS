<?php

namespace app\services;

use app\library\JobQueue;
use app\library\Log;
use app\library\Util;
use Yii;
use app\library\Api;
use app\library\Service;
use yii\base\Exception;
use yii\db\Connection;

/**
 * 多进程计算pid 2.0
 *
 * @author   haicheng
 */
class ForkCommand2 extends Service {

    private $_task_number = 5;

    private $_sql_select = null;

    public function __construct () {
        parent::__construct();
        $this->_task_number = getenv('task_number');
        $this->_sql_select  = 'SELECT * FROM url_pid_mapping_table WHERE status=:status LIMIT 0,' . $this->_task_number;

        return $this;
    }

    public function start () {

        if (!function_exists('pcntl_fork')) {
            Log::error("the pcntl extension was not found");

            exit;
        }

        $time_start = Util::microtimeFloat();

        $this->_doBuildPid();

        $time_end = Util::microtimeFloat();
        $time     = $time_end - $time_start;
        Log::info('DEBUG:span about seconds:' . $time);
    }


    private function _doBuildPid () {
        while (true) {

            $result_of_mapping = $this->_getPictureForBuidPid();
            if (false === $result_of_mapping) {

                break;
            }
            if (!is_array($result_of_mapping)) {

                continue;
            }

            $job = new JobQueue($result_of_mapping, 5);

            $job->run(function ($result_of_one = []) {
                try {
                    if (!empty($result_of_one)) {
                        //创建DB链接
                        $_db_settle = new Connection(Yii::$app->params['db_settle']);
                        $_db_settle->open();

                        $status_of_update = 1;
                        $result_of_one    = $result_of_one[0];
                        if (!empty($result_of_one['picture_url'])) {

                            $result_of_upload = Api::uploadPic($result_of_one['picture_url']);
                            if (false === $result_of_upload) {
                                $status_of_update = 3;
                                Log::info(__METHOD__ . ' 图片上传失败');
                            }

                            $result_of_update = $_db_settle->createCommand('UPDATE url_pid_mapping_table SET status = :status,picture_id = :picture_id WHERE auto_id=:auto_id')
                                ->bindValue(':status', $status_of_update)
                                ->bindValue(':picture_id', $result_of_upload['pic_id'])
                                ->bindValue(':auto_id', $result_of_one['auto_id'])->execute();
                            if (!$result_of_update) {

                                Log::info(__METHOD__ . ' 图片pid更新失败');
                            } else {

                                Log::info(__METHOD__ . ' 图片pid更新 ' . json_encode($result_of_upload));
                            }

                        }
                    }
                } catch (Exception $e) {
                    Log::error(__METHOD__ . ' 失败了:' . $e->getMessage());

                    return false;
                } finally {
                    Log::info(__METHOD__ . 'task DB close');

                    if(isset($_db_settle)){
                        $_db_settle->close();
                    }
                }
            });
        }
        Log::info('任务结束');
    }

    /**
     * _getPictureForBuidPid
     *
     * @return bool
     */
    private function _getPictureForBuidPid () {
        try {
            $_db_settle = new Connection(Yii::$app->params['db_settle']);
            $_db_settle->open();
            $result_of_mapping = $_db_settle->createCommand($this->_sql_select)->bindValue(':status', 0)->queryAll();
            if (!empty($result_of_mapping)) {

                return $result_of_mapping;
            } else {
                Log::info(__METHOD__ . ' 数据empty');

                return false;
            }
        } catch (Exception $e) {
            Log::error(__METHOD__ . ' 失败了:' . $e->getMessage());

            return false;
        } finally {
            Log::info(__METHOD__ . 'master DB close');

            if(isset($_db_settle)){
                $_db_settle->close();
            }
        }
    }

    public function mock () {

        $imgs       = [
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
        $count      = 100;
        $_db_settle = new Connection(Yii::$app->params['db_settle']);
        $_db_settle->open();
        while ($count-- > 0) {
            $_db_settle->createCommand()->insert('url_pid_mapping_table', [
                'picture_url' => $imgs[rand(0, 8)],
            ])->execute();
        }
        $_db_settle->close();
    }
}
