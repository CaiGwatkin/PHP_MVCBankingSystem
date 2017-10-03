<?php
namespace cgwatkin\a2\controller;

use cgwatkin\a2\model\AccountModel;
use cgwatkin\a2\model\AccountCollectionModel;
use cgwatkin\a2\model\Model;
use cgwatkin\a2\view\View;

/**
 * Class AccountController
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
        session_start();
        if (isset($_SESSION['username'])) {
            $view = new View('accountLoggedIn');
            echo $view->addData('username', $_SESSION['username'])
                ->addData('linkTo', function ($route,$params=[]) {
                    return $this->linkTo($route, $params);
                })
                ->render();
        } else {
            $view = new View('accountLogin');
            echo $view->render();
        }
    }

    /**
     * Account Login action
     *
     * Handles POST from login form.
     */
    public function loginAction()
    {
        if (isset($_POST['login'])) {
//            error_log("post received",0);
            $username = $_POST['username'];
            $account = new AccountModel();
            if (!$id = $account->checkLogin($username, $_POST['password'])) {
//                error_log("login failed",0);
                $view = new View('accountLoginFailed');
                echo $view->addData('username', $username)
                    ->render();
                return;
            }
            session_start();
            $_SESSION['userId'] = $id;
            $_SESSION['username'] = $username;
            if ($username == 'admin') {
//                error_log("admin login",0);
                header('Location: /account/list');
                $collection = new AccountCollectionModel();
                $accounts = $collection->getAccounts();
                $view = new View('accountList');
                echo $view->addData('accounts', $accounts)
                    ->addData(
                        'linkTo', function ($route,$params=[]) {
                        return $this->linkTo($route, $params);
                    }
                    )
                    ->render();
            }
            else {
//                error_log("other login",0);
                $account = new AccountModel();
                $account->load($id);
                // TODO: generate new "My Account" view
//                $view = new View('accountMyAccount');
//                echo $view->addData('account', $account)
//                    ->addData(
//                        'linkTo', function ($route,$params=[]) {
//                        return $this->linkTo($route, $params);
//                    }
//                    )
//                    ->render();
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
     * Account Index action
     */
    public function listAction()
    {
        $collection = new AccountCollectionModel();
        $accounts = $collection->getAccounts();
        $view = new View('accountList');
        echo $view->addData('accounts', $accounts)
            ->addData(
                'linkTo', function ($route,$params=[]) {
                    return $this->linkTo($route, $params);
                }
            )
            ->render();
    }
    /**
     * Account Create action
     */
    public function createAction() 
    {
        $account = new AccountModel();
        $names = ['Bob','Mary','Jon','Peter','Grace'];
        shuffle($names);
        $account->setName($names[0]); // will come from Form data
        $account->save();
        $id = $account->getId();
        $view = new View('accountCreated');
        echo $view->addData('accountId', $id)
            ->addData(
                'linkTo', function ($route,$params=[]) {
                    return $this->linkTo($route, $params);
                }
            )
                  ->render();
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
        $account->setName('Joe')->save(); // new name will come from Form data

    }

}
