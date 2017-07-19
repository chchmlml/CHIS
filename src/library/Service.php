<?php
/**
 * service
 *
 * @author   haicheng
 * @datetime 17/3/29 下午4:10
 */

namespace app\library;

class Service {

    private $_error = null;

    public function __construct () {

    }

    /**
     * @return string
     */
    public function getError () {
        return $this->_error;
    }

    /**
     * @param string $error
     */
    public function setError ($error) {
        $this->_error = $error;
    }

    /**
     * isError
     *
     * @return bool
     */
    public function isError () {
        return empty($this->_error);
    }
}