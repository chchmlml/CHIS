<?php

namespace app\services;

use app\library\Log;
use Yii;
use app\library\Api;
use app\library\Service;
use yii\base\Exception;
use yii\db\Connection;

class ForkCommand extends Service {

    /**
     * 是否在主进程
     */
    private static $_is_master = true;

    /**
     * 任务数量
     */
    private static $_task_number = 5;
    private static $_current_number = 0;

    private static $_task_id = 0;

    private static $_db_bp = null;
    private static $_db_settle = null;

    private $_cache_name = 'sina_da_cache_data';

    /**
     * @var \yii\caching\Cache
     */
    private static $_cache = null;

    public function __construct () {
        parent::__construct();

//        self::$_db_bp     = new Connection(Yii::$app->params['db_bp']);
        self::$_db_settle = new Connection(Yii::$app->params['db_settle']);
        self::$_cache = Yii::$app->cache;
//        self::$_db_bp     = new Connection(Yii::$app->params['db_bp']);
//        self::$_db_settle = new Connection(Yii::$app->params['db_settle']);
        return $this;
    }


    /**
     * Signal hander.
     *
     * @param int $signal
     */
    public function signalHandler ($signal) {
        switch ($signal) {
            // Stop.
            case SIGINT:
                log::warn("Program stopping...");
                break;
            // Show status.
            case SIGUSR2:
                echo "show status\n";
                break;
        }
    }

    /**
     * Install signal handler.
     *
     * @return void
     */
    public function installSignal () {
        if (function_exists('pcntl_signal')) {
            // stop
            pcntl_signal(SIGINT, array(
                __CLASS__,
                'signalHandler'
            ), false);
            // status
            pcntl_signal(SIGUSR2, array(
                __CLASS__,
                'signalHandler'
            ), false);
            // ignore
            pcntl_signal(SIGPIPE, SIG_IGN, false);
        }
    }

    public function start () {

        if (!function_exists('pcntl_fork')) {
            log::error("the pcntl extension was not found");
            exit;
        }


        $this->_doBuildPid();
    }

    private function _doBuildPid(){
        while (true) {

            $this->_getPictureForBuidPid();

            Log::info('NOW FORK NUMBER IS ' . self::$_current_number);
            Log::info('NOW TASK NUMBER IS ' . self::$_task_number);

            $result_of_cache = self::$_cache->get($this->_cache_name);
            if (!empty($result_of_cache)) {

                $pid = pcntl_fork();

                if ($pid > 0) {
                    // 暂时没用
                    //self::$taskpids[$taskid] = $pid;
                } elseif (0 === $pid) {

                } else {
                    $this->_BuildPid();
                    exit;
                }
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
                ->createCommand('SELECT auto_id,picture_url FROM url_pid_mapping_table WHERE status=:status LIMIT 0,100')
                ->bindValue(':status', 0)->queryAll();

            if(empty($result_of_mapping)){
                $result_of_cache = self::$_cache->get($this->_cache_name);
                if(!empty($result_of_cache)){
                    return $this;
                } else {
                    return false;
                }
            }

            if ($this->_islock()) {

                return $this;
            }
            $this->_lock();
            $result_of_cache = self::$_cache->get($this->_cache_name);
            if(!empty($result_of_cache)){
                $result_of_mapping = array_merge($result_of_cache, $result_of_mapping);
            }
            self::$_cache->set($this->_cache_name, $result_of_mapping);
            $this->_unlock();
        } catch (Exception $e) {

            Log::error(__METHOD__ . ' 获取失败了:' . $e->getMessage());
            $this->setError('获取失败了:' . $e->getMessage());

            return $this;
        }

        return $this;
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

            self::$_current_number++;
            if (self::$_current_number >= self::$_task_number) {
                pcntl_wait($status);
                self::$_current_number--;
            }

        } else {
            $this->_BuildPid();
            exit;
        }
    }

    /**
     * _BuildPid
     *
     *
     * @return $this|bool
     */
    private function _BuildPid () {
        try {
            if ($this->_islock()) {
                exit;
            }
            $this->_lock();
            $result_of_cache = self::$_cache->get($this->_cache_name);
            if (!empty($result_of_cache)) {
                $result_of_one = array_shift($result_of_cache);
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
                            self::$_cache->set($this->_cache_name, $result_of_cache);
                            Log::info(__METHOD__ . ' 图片pid ' . json_encode($result_of_upload));
                        }

                    }
                }
            }
            $this->_unlock();
        } catch (Exception $e) {

            Log::info(__METHOD__ . ' 更新失败了:' . $e->getMessage());
            $this->setError(__METHOD__ . '更新失败了:' . $e->getMessage());

            return false;
        }

        return $this;
    }

    private function _lock(){
        self::$_cache->set('lock', 1, 20);
    }

    private function _unlock(){
        self::$_cache->set('lock', 0, 20);
    }

    private function _islock () {
        $lock_status = self::$_cache->get('lock');

        if(false === $lock_status){
            //没有锁
            return false;
        }
        return (1 === $lock_status) ? true : false;
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
        $count = 10000;
        while ($count-- > 0) {
            self::$_db_settle->createCommand()->insert('url_pid_mapping_table', [
                'picture_url' => $imgs[rand(0,8)],
            ])->execute();
        }

    }
}
