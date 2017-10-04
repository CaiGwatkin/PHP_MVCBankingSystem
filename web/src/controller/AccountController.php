<?php
namespace cgwatkin\a2\controller;

use cgwatkin\a2\model\MySQLQueryException;
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
        new Model(); // create table if not exist
        $view = new View('accountLogin');
        session_start();
        if (isset($_SESSION['username'])) {
            $view->addData('username', $_SESSION['username'])
                ->addData('linkTo', function ($route,$params=[]) {
                    return $this->linkTo($route, $params);
                });
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
                    ->setUsername($username)
                    ->checkLogin($_POST['password']);
            }
            catch (MySQLQueryException $ex) {
                error_log($ex->getMessage());
                $view = new View('error');
                echo $view->addData('errorCode', '500 Internal Server Error')
                    ->addData('errorMessage', 'MySQL error')
                    ->addData('linkTo', function ($route, $params = []) {
                        return $this->linkTo($route, $params);
                    })
                    ->render();
                return;
            }
            if (!$account) {
                $view = new View('accountLogin');
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
            $view = new View('accountLogin');
            session_start();
            if (isset($_SESSION['username'])) {
                $view->addData('loggedIn', true)
                    ->addData('username', $_SESSION['username'])
                    ->addData('linkTo', function ($route,$params=[]) {
                        return $this->linkTo($route, $params);
                    });
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
        $view = new View('accountLoggedOut');
        echo $view->addData('linkTo', function ($route,$params=[]) {
                return $this->linkTo($route, $params);
            })
            ->render();
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
            $view = new View('accountList');
            echo $view->addData('accounts', $accounts)
                ->addData('linkTo', function ($route, $params = []) {
                    return $this->linkTo($route, $params);
                })
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
        $view = new View('accountAccessDenied');
        echo $view->addData('linkTo', function ($route, $params = []) {
                return $this->linkTo($route, $params);
            })
            ->render();
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
                } catch (MySQLQueryException $ex) {
                    error_log($ex->getMessage());
                    $view = new View('error');
                    echo $view->addData('errorCode', '500 Internal Server Error')
                        ->addData('errorMessage', 'Account name "'.$username.'" already exists.')
                        ->addData('linkTo', function ($route, $params = []) {
                            return $this->linkTo($route, $params);
                        })
                        ->render();
                    return;
                }
                $view = new View('accountCreate');
                echo $view->addData('account', $account)
                    ->addData('linkTo', function ($route, $params = []) {
                        return $this->linkTo($route, $params);
                    })
                    ->render();
            }
            else {
                $view = new View('accountCreate');
                echo $view->addData('linkTo', function ($route, $params = []) {
                        return $this->linkTo($route, $params);
                    })
                    ->render();
            }
        }
        else {
            $view = new View('accountAccessDenied');
            echo $view->addData('linkTo', function ($route, $params = []) {
                    return $this->linkTo($route, $params);
                })
                ->render();
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
        }
        catch (MySQLQueryException $ex) {
            $message = $ex->getMessage();
            error_log($message);
            $view = new View('error');
            echo $view->addData('errorCode', '500 Internal Server Error')
                ->addData('errorMessage', $message)
                ->addData('linkTo', function ($route, $params = []) {
                    return $this->linkTo($route, $params);
                })
                ->render();
            return;
        }
        $view = new View('accountDeleted');
        echo $view->addData('accountId', $id)
            ->addData('linkTo', function ($route,$params=[]) {
                    return $this->linkTo($route, $params);
                })
            ->render();
    }
    /**
     * Account Update action
     *
     * @param int $id Account id to be updated
     */
    /*public function updateAction($id)
    {
        try {
            $account = (new AccountModel())->load($id);
            $account->setUsername('Joe')->save(); // new name will come from Form data
        }
        catch (MySQLQueryException $ex) {
            $message = $ex->getMessage();
            error_log($message);
            $view = new View('error');
            echo $view->addData('errorCode', '500 Internal Server Error')
                ->addData('errorMessage', $message)
                ->addData('linkTo', function ($route, $params = []) {
                    return $this->linkTo($route, $params);
                })
                ->render();
            return;
        }

    }*/

}
