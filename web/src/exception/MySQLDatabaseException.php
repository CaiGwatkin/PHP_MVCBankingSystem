<?php

namespace cgwatkin\a2\exception;

/**
 * Class MySQLDatabaseException
 *
 * Thrown when MySQL database cannot be loaded.
 *
 * @package cgwatkin/a2
 * @author  Cai Gwatkin <caigwatkin@gmail.com>
 */
class MySQLDatabaseException extends \Exception
{
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