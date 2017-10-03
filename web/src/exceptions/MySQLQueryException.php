<?php

namespace cgwatkin\a2\exception;

/**
 * Class NoMySQLException
 *
 * Thrown when a MySQL query returns null.
 *
 * @package cgwatkin/a2
 * @author  Cai Gwatkin <caigwatkin@gmail.com>
 */
class MySQLQueryException extends \Exception
{
    /**
     * MySQLQueryException constructor.
     *
     * @param string $message The exception message.
     * @param int $code The code of the exception.
     */
    public function MySQLQueryException($message, $code = 0)
    {
        parent::__construct($message, $code);
        error_log($message);
    }
}