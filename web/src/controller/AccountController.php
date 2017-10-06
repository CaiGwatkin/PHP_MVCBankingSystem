<?php
/*
 * Gwatkin, 15146508
 */
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
            $_SESSION['accountID'] = $account->getID();
            $_SESSION['username'] = $username;
            if ($this->userIsAdmin()) {
                $this->redirectAction('/account/list');
            }
            else {
                $this->redirectAction('/transfer/list');
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
            $page = $_GET['page']??1;
            $limit = 10;
            $offset = ($page - 1) * $limit;
            try {
                $accountCollection = new AccountCollectionModel($limit, $offset);
                $accounts = $accountCollection->getObjects();
                $view = new View('accountList');
                echo $view->addData('accounts', $accounts)
                    ->addData('numAccounts', $accountCollection->getNum())
                    ->addData('page', $page)
                    ->render();
            }
            catch (MySQLQueryException $ex) {
                $this->errorAction(self::$INTERNAL_SERVER_ERROR_MESSAGE, 'MySQL error');
                return;
            }
            catch (LoadTemplateException $ex) {
                $this->errorAction(self::$INTERNAL_SERVER_ERROR_MESSAGE, $ex->getMessage());
                return;
            }
        }
        else {
            $this->redirectAction('/accessDenied');
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
                    $view = new View('accountCreate');
                    echo $view->addData('account', $account)
                        ->render();
                }
                catch (MySQLQueryException $ex) {
                    $this->errorAction(self::$INTERNAL_SERVER_ERROR_MESSAGE, 'Account name "'.$username.'" already exists.');
                    return;
                }
                catch (LoadTemplateException $ex) {
                    $this->errorAction(self::$INTERNAL_SERVER_ERROR_MESSAGE, $ex->getMessage());
                    return;
                }
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
            $this->redirectAction('/accessDenied');
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
            $this->redirectAction('/accessDenied');
        }
    }
}
