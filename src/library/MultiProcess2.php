<?php

namespace app\library;

/**
 * @author:haicheng
 * @date  2014-01-14
 */
class MultiProcess2 {


    //最大队列长度
    private $max_size;

    private $current_size;

    //生产者
    private $producer;

    //消费者
    private $worker;


    /**
     * 构造函数
     *
     * @param        $producer 需要创建的消费者类名
     * @param string $worker   需要创建的消费者类名
     * @param int    $max_size 最大子进程数量
     *
     * @throws \app\library\ApiException
     */
    public function __construct ($producer, $worker, $max_size = 10) {

        if (!function_exists('pcntl_fork')) {

            throw new ApiException("cannot find func pcntl_fork");
        }
        $this->producer     = new $producer;
        $this->worker       = $worker;
        $this->max_size     = $max_size;
        $this->current_size = 0;
    }

    public function start () {

        try{

            $producer_pid = pcntl_fork();
            if ($producer_pid == -1) {

                Log::info(__METHOD__ . '  could not fork');
                throw new ApiException("could not fork");
            } elseif ($producer_pid) {

                while (true) {

                    $pid = pcntl_fork();
                    if ($pid == -1) {

                        Log::info(__METHOD__ . '  could not fork');
                        throw new ApiException("could not fork");
                    } elseif ($pid) {

                        $this->current_size++;

                        if ($this->current_size >= $this->max_size) {
                            $sunPid = pcntl_wait($status);
                            $this->current_size--;
                        }

                        Log::info('NOW PROCESS NUMBER IS ' . $this->current_size);
                        exit(0);
                    } else {

                        $pid  = posix_getpid();
                        //Log::info(__METHOD__ . '进程_' . $pid . ' start');
                        //                    $worker = new $this->worker('进程_' . $pid . '_' . $this->current_size);
                        //                    $worker->run();
                        exit(0);
                    }
                }

            } else {

                //$this->producer->run();
                //exit(0);
            }
        }catch (Exception $e) {

            Log::info(__METHOD__ . ' 更新失败了:' . $e->getMessage());

        }

    }
}