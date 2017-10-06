<?php
namespace cgwatkin\a2\controller;

use cgwatkin\a2\exception\LoadTemplateException;
use cgwatkin\a2\exception\MySQLQueryException;
use cgwatkin\a2\model\TransferCollectionModel;
use cgwatkin\a2\view\View;

/**
 * Class TransferController
 *
 * Base code provided by Andrew Gilman <a.gilman@massey.ac.nz>
 *
 * @package cgwatkin/a2
 * @author  Cai Gwatkin <caigwatkin@gmail.com>
 */
class TransferController extends Controller
{
    /**
     * Transfer List action
     *
     * Lists transfers in system for currently logged-in account.
     */
    public function listAction()
    {
        if ($this->userIsLoggedIn()) {
            $page = $_GET['page']??1;
            $limit = 10;
            $offset = ($page - 1) * $limit;
            $accountID = $_SESSION['accountID'];
            try {
                $transferCollection = new TransferCollectionModel($limit, $offset, $accountID);
                $transfers = $transferCollection->getObjects();
                $view = new View('transferList');
                echo $view->addData('transfers', $transfers)
                    ->addData('numTransfers', $transferCollection->getNum())
                    ->addData('username', $_SESSION['username'])
                    ->addData('accountID', $accountID)
                    ->addData('page', $page)
                    ->render();
            }
            catch (MySQLQueryException $ex) {
                $this->errorAction(self::$INTERNAL_SERVER_ERROR_MESSAGE, 'MySQL error '.$ex->getMessage());
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
