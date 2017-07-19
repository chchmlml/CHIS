<?php

namespace app\library;

use yii\base\UserException;

/**
 * ApiException.
 *
 * @author haicheng
 */
class ApiException extends UserException
{
    /**
     * Constructor.
     * @param string $message error message
     * @param int $code error code
     * @param \Exception $previous The previous exception used for the exception chaining.
     */
    public function __construct($message = null, $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
