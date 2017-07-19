<?php

namespace app\services;

use app\library\Log;
use app\library\Util;
use Yii;
use app\library\Api;
use app\library\Service;
use yii\base\Exception;
use yii\db\Connection;

class SingleCommand extends Service {

    /**
     * 是否在主进程
     */
    private static $_is_master = true;

    /**
     * 任务数量
     */
    private static $_task_number = 5;

    private static $_task_id = 0;

    private static $_db_bp = null;
    private static $_db_settle = null;

    public function __construct () {
        parent::__construct();

        self::$_db_bp     = new Connection(Yii::$app->params['db_bp']);
        self::$_db_settle = new Connection(Yii::$app->params['db_settle']);
//        self::$_db_bp     = new Connection(Yii::$app->params['db_bp']);
//        self::$_db_settle = new Connection(Yii::$app->params['db_settle']);
        return $this;
    }


    public function start () {

        if (!function_exists('pcntl_fork')) {
            log::error("the pcntl extension was not found");
            exit;
        }


        $time_start = Util::microtimeFloat();

        $this->_doBuildPid();

        $time_end = Util::microtimeFloat();
        $time = $time_end - $time_start;
        Log::info('span about seconds:' . $time);
    }

    private function _doBuildPid(){
        while (false !== ($result_of_mapping = $this->_getPictureForBuidPid())) {

            foreach ($result_of_mapping as $task) {

                $this->_BuildPid($task);
            }
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

            $result_of_mapping = self::$_db_settle
                ->createCommand('SELECT * FROM url_pid_mapping_table WHERE status=:status LIMIT 0,100')
                ->bindValue(':status', 0)->queryAll();
            if (!empty($result_of_mapping)) {

                return $result_of_mapping;
            } else {

                return false;
            }
        } catch (Exception $e) {

            Log::error(__METHOD__ . ' 获取失败了:' . $e->getMessage());
            $this->setError('获取失败了:' . $e->getMessage());

            return false;
        }

        return false;
    }

    private function _forkTask ($task_id) {
        $pid = pcntl_fork();

        if ($pid > 0) {
            // 暂时没用
            //self::$taskpids[$taskid] = $pid;
        } elseif (0 === $pid) {

            // 初始化子进程参数
            self::$_task_id   = $task_id;
            self::$_is_master = false;

            $this->_doBuildPid();

            exit(0);
        } else {

            Log::info('Fork a bad process.');
            exit;
        }
    }

    /**
     * _BuildPid
     *
     * @param $result_of_one
     *
     * @return $this|bool
     */
    private function _BuildPid ($result_of_one) {
        try {

            if (!empty($result_of_one)) {
                if (!empty($result_of_one['picture_url'])) {

                    $result_of_upload = Api::uploadPic($result_of_one['picture_url']);
                    if (false === $result_of_upload) {

                        Log::info(__METHOD__ . ' 图片上传失败');
                        $this->setError('图片上传失败');
                    } else {
                        $result_of_update = self::$_db_settle
                            ->createCommand('UPDATE url_pid_mapping_table SET status = :status,picture_id = :picture_id WHERE auto_id=:auto_id ')
                            ->bindValue(':status', 1)
                            ->bindValue(':picture_id', $result_of_upload['pic_id'])
                            ->bindValue(':auto_id', $result_of_one['auto_id'])->execute();
                        if (!$result_of_update) {

                            Log::info(__METHOD__ . ' 图片pid更新失败');
                            $this->setError('图片pid更新失败');
                        } else {

                            Log::info(__METHOD__ . ' 图片pid ' . json_encode($result_of_upload));
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
//            self::$_db_bp->createCommand('COMMIT WORK')->execute();
        }

        return $this;
    }
}
