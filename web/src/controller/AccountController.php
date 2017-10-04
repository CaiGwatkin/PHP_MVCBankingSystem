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
        session_start();
        if (isset($_SESSION['username'])) {
            $view->addData('username', $_SESSION['username']);
        } else {
            $view->addData('username', null);
        }
        echo $view->render();
    }

    /**
     * Account Error action
     *
     * @param string $error The error (code + type).
     * @param string $message The error message.
     */
    private function errorAction(string $error, string $message)
    {
        error_log($error.': '.$message);
        try {
            $view = new View('error');
        }
        catch (LoadTemplateException $ex) {
            echo self::$INTERNAL_SERVER_ERROR_MESSAGE.': '.$ex->getMessage();
            return;
        }
        echo $view->addData('error', $error)
            ->addData('errorMessage', $message)
            ->render();
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
                    ->setUsername($username)
                    ->checkLogin($_POST['password']);
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
            if ($username == 'admin') {
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
                $view = new View('accountLogin');
            }
            catch (LoadTemplateException $ex) {
                $this->errorAction(self::$INTERNAL_SERVER_ERROR_MESSAGE, $ex->getMessage());
                return;
            }
            session_start();
            if (isset($_SESSION['username'])) {
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
        }
        catch (LoadTemplateException $ex) {
            $this->errorAction(self::$INTERNAL_SERVER_ERROR_MESSAGE, $ex->getMessage());
            return;
        }
        echo $view->render();
    }
    
    /**
     * Account List action
     *
     * Lists accounts in system if user is admin.
     */
    public function listAction()
    {
        session_start();
        if (isset($_SESSION['username']) && $_SESSION['username'] == 'admin') {
            $collection = new AccountCollectionModel();
            $accounts = $collection->getAccounts();
            try {
                $view = new View('accountList');
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
     * Access Denied action
     *
     * Displays access denied view.
     */
    public function accessDeniedAction()
    {
        try {
            $view = new View('accountAccessDenied');
        }
        catch (LoadTemplateException $ex) {
            $this->errorAction(self::$INTERNAL_SERVER_ERROR_MESSAGE, $ex->getMessage());
            return;
        }
        echo $view->render();
    }

    /**
     * Account Create action
     *
     * If user is admin and request is not POST, display input for new account data.
     * If user is admin and request is POST, try to create account and display new account.
     */
    public function createAction() 
    {
        $account = new AccountModel();
        session_start();
        if (isset($_SESSION['username']) && $_SESSION['username'] == 'admin') {
            if (isset($_POST['create'])) {
                $username = $_POST['username'];
                try {
                    $account->setUsername($username)
                        ->save($_POST['password']);
                    $view = new View('accountCreate');
                } catch (MySQLQueryException $ex) {
                    $this->errorAction(self::$INTERNAL_SERVER_ERROR_MESSAGE, 'Account name "'.$username.'" already exists.');
                    return;
                }
                catch (LoadTemplateException $ex) {
                    $this->errorAction(self::$INTERNAL_SERVER_ERROR_MESSAGE, $ex->getMessage());
                    return;
                }
                echo $view->addData('account', $account)
                    ->render();
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
            try {
                $view = new View('accountAccessDenied');
            }
            catch (LoadTemplateException $ex) {
                $this->errorAction(self::$INTERNAL_SERVER_ERROR_MESSAGE, $ex->getMessage());
                return;
            }
            echo $view->render();
        }
    }

    /**
     * Account Delete action
     *
     * @param int $id Account id to be deleted
     */
    public function deleteAction($id)
    {
        try {
            (new AccountModel())->load($id)->delete();
            $view = new View('accountDeleted');
        }
        catch (MySQLQueryException $ex) {
            $this->errorAction(self::$INTERNAL_SERVER_ERROR_MESSAGE, $ex->getMessage());
            return;
        }
        catch (LoadTemplateException $ex) {
            $this->errorAction(self::$INTERNAL_SERVER_ERROR_MESSAGE, $ex->getMessage());
            return;
        }
        echo $view->addData('accountId', $id)
            ->render();
    }
}
