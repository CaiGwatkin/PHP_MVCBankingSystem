<?php

namespace cgwatkin\a2\exception;

/**
 * Class NoMySQLException
 *
 * Used when MySQL cannot be loaded.
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
    }
}