<?php

namespace cgwatkin\a2\controller;

/**
 * Class Controller
 *
 * Base code provided by Andrew Gilman <a.gilman@massey.ac.nz>
 *
 * @package cgwatkin/a2
 * @author  Cai Gwatkin <caigwatkin@gmail.com>
 */
class Controller
{
    /**
     * Redirect browser to new URL.
     *
     * @param string $url The new URL to be redirected to.
     * @param int $statusCode The HTTP status code for redirection. 303 by default.
     */
    public function redirectAction($url, $statusCode = 303)
    {
        header('Location: ' . $url, true, $statusCode);
        die();
    }
}