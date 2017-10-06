<?php
/*
 * Gwatkin, 15146508
 */

namespace cgwatkin\a2\exception;

/**
 * Class LoadTemplateException
 *
 * Thrown when a template cannot be loaded.
 *
 * @package cgwatkin/a2
 * @author  Cai Gwatkin <caigwatkin@gmail.com>
 */
class LoadTemplateException extends \Exception
{
    /**
     * LoadTemplateException constructor.
     *
     * @param string $message The exception message.
     * @param int $code The code of the exception.
     */
    public function LoadTemplateException($message, $code = 0)
    {
        parent::__construct($message, $code);
    }
}