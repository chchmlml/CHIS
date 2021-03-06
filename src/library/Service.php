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
	
	/**
	 * log
	 *
	 * @param string $message
	 */
    protected function log($message = ''){
	    $message = is_array($message) ? var_export($message, true) : $message;
	    
	    $blue   = "\e[34m";
	    $lblue  = "\e[36m";
	    $cln    = "\e[0m";
	    $green  = "\e[92m";
	    $fgreen = "\e[32m";
	    $red    = "\e[91m";
	    $bold   = "\e[1m";
	    
	    $message = __METHOD__ . ' ' . $message;
    	echo $bold . $blue . $message . "\n";
	    Log::info($message);
    }
}