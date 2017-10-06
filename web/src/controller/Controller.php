<?php
/*
 * Gwatkin, 15146508
 */
namespace cgwatkin\a2\controller;

use cgwatkin\a2\exception\LoadTemplateException;
use cgwatkin\a2\view\View;

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
     * @var string The message for internal server errors.
     */
    static $INTERNAL_SERVER_ERROR_MESSAGE = '500 Internal Server Error';

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
     * Error action
     *
     * Creates an error view to display error message to user.
     *
     * @param string $error The error (code + type).
     * @param string $message The error message.
     */
    function errorAction(string $error, string $message)
    {
        try {
            error_log($error.': '.$message);
            $view = new View('error');
            echo $view->addData('error', $error)
                ->addData('errorMessage', $message)
                ->render();
        }
        catch (LoadTemplateException $ex) {
            echo self::$INTERNAL_SERVER_ERROR_MESSAGE.': '.$ex->getMessage();
            return;
        }
    }

    /**
     * Access Denied action
     *
     * Displays access denied view.
     */
    public function accessDeniedAction()
    {
        try {
            $view = new View('accountAccessDenied');
            echo $view->render();
        }
        catch (LoadTemplateException $ex) {
            $this->errorAction(self::$INTERNAL_SERVER_ERROR_MESSAGE, $ex->getMessage());
            return;
        }
    }

    /**
     * Checks if user is logged in as admin.
     *
     * @return bool Whether the current user is admin.
     */
    function userIsAdmin()
    {
        session_start();
        return $this->userIsLoggedIn() && $_SESSION['username'] == 'admin';
    }

    /**
     * Checks if any user is logged in.
     *
     * @return bool Whether any user is logged in.
     */
    function userIsLoggedIn()
    {
        session_start();
        return isset($_SESSION['username']);
    }
}
