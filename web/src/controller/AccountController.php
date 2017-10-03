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
     *
     * Handles POST from login form.
     */
    public function loginAction()
    {
        if (isset($_POST['login'])) {
            $username = $_POST['username'];
            $account = new AccountModel();
            if (!$id = $account->checkLogin($username, $_POST['password'])) {
                $view = new View('accountLoginFailed');
                echo $view->addData('username', $username)
                    ->render();
                return;
            }
            session_start();
            $_SESSION['userId'] = $id;
            $_SESSION['username'] = $username;
            if ($username == 'admin') {
                header('Location: /account/list');
                $collection = new AccountCollectionModel();
                $accounts = $collection->getAccounts();
                $view = new View('accountList');
                echo $view->addData('accounts', $accounts)
                    ->addData('linkTo', function ($route,$params=[]) {
                        return $this->linkTo($route, $params);
                    })
                    ->render();
            }
            else {
                $account = new AccountModel();
                $account->load($id);
                // TODO: generate new "My Account" view
            }
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
     * Access Error action
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
                        ->setUnformedPassword($_POST['password'])
                        ->save();
                } catch (MySQLQueryException $ex) {
                    error_log($ex->getMessage());
                    $view = new View('error');
                    echo $view->addData('accountUsername', $username)
                        ->addData('errorCode', '500 Internal Server Error')
                        ->addData('errorMessage', 'Account name "'.$username.'" already exists.')
                        ->addData('linkTo', function ($route, $params = []) {
                            return $this->linkTo($route, $params);
                        })
                        ->render();
                    return;
                }
                $id = $account->getId();
                $username = $account->getUsername();
                $view = new View('accountCreate');
                echo $view->addData('accountId', $id)
                    ->addData('accountUsername', $username)
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
        (new AccountModel())->load($id)->delete();
        $view = new View('accountDeleted');
        echo $view->addData('accountId', $id)
            ->addData(
                'linkTo', function ($route,$params=[]) {
                    return $this->linkTo($route, $params);
                }
            )
            ->render();
    }
    /**
     * Account Update action
     *
     * @param int $id Account id to be updated
     */
    public function updateAction($id) 
    {
        $account = (new AccountModel())->load($id);
        $account->setUsername('Joe')->save(); // new name will come from Form data

    }

}
