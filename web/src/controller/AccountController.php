<?php
namespace cgwatkin\a2\controller;

use cgwatkin\a2\exception\LoadTemplateException;
use cgwatkin\a2\exception\MySQLDatabaseException;
use cgwatkin\a2\exception\MySQLQueryException;
use cgwatkin\a2\model\AccountModel;
use cgwatkin\a2\model\AccountCollectionModel;
use cgwatkin\a2\model\Model;
use cgwatkin\a2\view\View;

/**
 * Class AccountController
 *
 * Base code provided by Andrew Gilman <a.gilman@massey.ac.nz>
 *
 * @package cgwatkin/a2
 * @author  Cai Gwatkin <caigwatkin@gmail.com>
 */
class AccountController extends Controller
{
    /**
     * @var string The message for internal server errors.
     */
    private static $INTERNAL_SERVER_ERROR_MESSAGE = '500 Internal Server Error';

    /**
     * Account Index action
     *
     * Displays login page if user not logged in.
     * Displays logged in page if user logged in.
     */
    public function indexAction()
    {
        try {
            new Model(); // create table if not exist
            $view = new View('accountLogin');
        }
        catch (MySQLDatabaseException $ex) {
            $this->errorAction(self::$INTERNAL_SERVER_ERROR_MESSAGE, $ex->getMessage());
            return;
        }
        catch (LoadTemplateException $ex) {
            $this->errorAction(self::$INTERNAL_SERVER_ERROR_MESSAGE, $ex->getMessage());
            return;
        }
        if ($this->userIsLoggedIn()) {
            $view->addData('username', $_SESSION['username']);
        } else {
            $view->addData('username', null);
        }
        echo $view->render();
    }

    /**
     * Account Login action
     */
    public function loginAction()
    {
        if (isset($_POST['login'])) {
            $username = $_POST['username'];
            try {
                $account = (new AccountModel())
                    ->checkLogin($username, $_POST['password']);
            }
            catch (MySQLQueryException $ex) {
                $this->errorAction(self::$INTERNAL_SERVER_ERROR_MESSAGE, 'MySQL error');
                return;
            }
            if (!$account) {
                try {
                    $view = new View('accountLogin');
                }
                catch (LoadTemplateException $ex) {
                    $this->errorAction(self::$INTERNAL_SERVER_ERROR_MESSAGE, $ex->getMessage());
                    return;
                }
                echo $view->addData('username', $username)
                    ->render();
                return;
            }
            $username = $account->getUsername();
            session_start();
            $_SESSION['userId'] = $account->getId();
            $_SESSION['username'] = $username;
            if ($this->userIsAdmin()) {
                $this->redirectAction('/account/list');
            }
            else {
                // TODO: generate new "My Account" view
                // PLACEHOLDER
                $this->redirectAction('/account/list');
            }
        }
        else {
            try {
                new Model(); // create table if not exist
                $view = new View('accountLogin');
            }
            catch (MySQLDatabaseException $ex) {
                $this->errorAction(self::$INTERNAL_SERVER_ERROR_MESSAGE, $ex->getMessage());
                return;
            }
            catch (LoadTemplateException $ex) {
                $this->errorAction(self::$INTERNAL_SERVER_ERROR_MESSAGE, $ex->getMessage());
                return;
            }
            if ($this->userIsLoggedIn()) {
                $view->addData('loggedIn', true)
                    ->addData('username', $_SESSION['username']);
            } else {
                $view->addData('username', null);
            }
            echo $view->render();
        }
    }
    
    /**
     * Account Logout action
     *
     * Destroys the session, logging the user out.
     */
    public function logoutAction()
    {
        session_start();
        session_destroy();
        try {
            $view = new View('accountLoggedOut');
            echo $view->render();
        }
        catch (LoadTemplateException $ex) {
            $this->errorAction(self::$INTERNAL_SERVER_ERROR_MESSAGE, $ex->getMessage());
        }
    }
    
    /**
     * Account List action
     *
     * Lists accounts in system if user is admin.
     */
    public function listAction()
    {
        if ($this->userIsAdmin()) {
            try {
                $collection = new AccountCollectionModel();
                $accounts = $collection->getAccounts();
                $view = new View('accountList');
            }
            catch (MySQLQueryException $ex) {
                $this->errorAction(self::$INTERNAL_SERVER_ERROR_MESSAGE, 'MySQL error');
                return;
            }
            catch (LoadTemplateException $ex) {
                $this->errorAction(self::$INTERNAL_SERVER_ERROR_MESSAGE, $ex->getMessage());
                return;
            }
            echo $view->addData('accounts', $accounts)
                ->render();
        }
        else {
            $this->redirectAction('/account/accessDenied');
        }
    }

    /**
     * Account Create action
     *
     * If user is admin and request is not POST, display input for new account data.
     * If user is admin and request is POST, try to create account and display new account.
     */
    public function createAction() 
    {
        if ($this->userIsAdmin()) {
            if (isset($_POST['create'])) {
                $username = $_POST['username'];
                try {
                    $account = new AccountModel();
                    $account->setUsername($username)
                        ->setPassword($_POST['password'])
                        ->save();
                    if (!$account) {
                        $this->errorAction(self::$INTERNAL_SERVER_ERROR_MESSAGE,
                            'Account creation failed. Did you enter a username?');
                        return;
                    }
                    else {
                        $view = new View('accountCreate');
                        $view->addData('account', $account);
                    }
                }
                catch (MySQLQueryException $ex) {
                    $this->errorAction(self::$INTERNAL_SERVER_ERROR_MESSAGE, 'Account name "'.$username.'" already exists.');
                    return;
                }
                catch (LoadTemplateException $ex) {
                    $this->errorAction(self::$INTERNAL_SERVER_ERROR_MESSAGE, $ex->getMessage());
                    return;
                }
                echo $view->render();
            }
            else {
                try {
                    $view = new View('accountCreate');
                }
                catch (LoadTemplateException $ex) {
                    $this->errorAction(self::$INTERNAL_SERVER_ERROR_MESSAGE, $ex->getMessage());
                    return;
                }
                echo $view->render();
            }
        }
        else {
            $this->redirectAction('/account/accessDenied');
        }
    }

    /**
     * Account Delete action
     *
     * @param int $id Account id to be deleted
     */
    public function deleteAction($id)
    {
        if ($this->userIsAdmin()) {
            try {
                $account = (new AccountModel())->load($id);
                if (!$account) {
                    $view = new View('accountDeleted');
                    echo $view->addData('accountId', $id)
                        ->render();
                }
                else {
                    $account->delete();
                    $view = new View('accountDeleted');
                    echo $view->addData('accountExists', true)
                        ->addData('accountId', $id)
                        ->render();
                }
            }
            catch (MySQLQueryException $ex) {
                $this->errorAction(self::$INTERNAL_SERVER_ERROR_MESSAGE, $ex->getMessage());
                return;
            }
            catch (LoadTemplateException $ex) {
                $this->errorAction(self::$INTERNAL_SERVER_ERROR_MESSAGE, $ex->getMessage());
                return;
            }
        }
        else {
            $this->redirectAction('/account/accessDenied');
        }
    }

    /**
     * Account Error action
     *
     * Creates an error view to display error message to user.
     *
     * @param string $error The error (code + type).
     * @param string $message The error message.
     */
    private function errorAction(string $error, string $message)
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
    private function userIsAdmin()
    {
        session_start();
        return isset($_SESSION['username']) && $_SESSION['username'] == 'admin';
    }

    /**
     * Checks if any user is logged in.
     *
     * @return bool Whether any user is logged in.
     */
    private function userIsLoggedIn()
    {
        session_start();
        return isset($_SESSION['username']);
    }
}
