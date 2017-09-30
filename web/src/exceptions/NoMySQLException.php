<?php

namespace cgwatkin\a2;

/**
 * Class NoMySQLException
 *
 * Used when MySQL cannot be loaded.
 *
 * @package cgwatkin/a2
 * @author  Cai Gwatkin <caigwatkin@gmail.com>
 */
class NoMySQLException extends \Exception {
    /**
     * NoMySQLException constructor.
     *
     * @param string $message The exception message.
     * @param int $code The code of the exception.
     */
    public function NoMySQLException($message, $code = 0)
    {
        parent::__construct($message, $code);
    }
}