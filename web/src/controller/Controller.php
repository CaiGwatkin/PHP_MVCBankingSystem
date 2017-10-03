<?php

namespace cgwatkin\a2\controller;

/**
 * Class Controller
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
    
    /**
     * Generate a link URL for a named route
     *
     * @param string $route  Named route to generate the link URL for
     * @param array  $params Any parameters required for the route
     *
     * @return string  URL for the route
     */
    static function linkTo($route, $params=[])
    {
        // cheating here! What is a better way of doing this?
        $router = $GLOBALS['router'];
        return $router->generate($route, $params);
    }
}