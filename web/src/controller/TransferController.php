<?php
/*
 * Gwatkin, 15146508
 */
namespace cgwatkin\a2\controller;

use cgwatkin\a2\exception\LoadTemplateException;
use cgwatkin\a2\exception\MySQLQueryException;
use cgwatkin\a2\model\AccountCollectionModel;
use cgwatkin\a2\model\AccountModel;
use cgwatkin\a2\model\TransferCollectionModel;
use cgwatkin\a2\model\TransferModel;
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
                $balance = (new AccountModel())->load($accountID)->getBalance();
                $transferCollection = new TransferCollectionModel($accountID, $limit, $offset);
                $transfers = $transferCollection->getObjects();
                $view = new View('transferList');
                echo $view->addData('transfers', $transfers)
                    ->addData('numTransfers', $transferCollection->getNum())
                    ->addData('username', $_SESSION['username'])
                    ->addData('accountID', $accountID)
                    ->addData('balance', $balance)
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
     * Transfer Make action
     *
     * If request is not POST, display input for new transfer data.
     * If request is POST, try to create transfer and display new transfer.
     */
    public function makeAction()
    {
        if ($this->userIsLoggedIn()) {
            $username = $_SESSION['username'];
            if (isset($_POST['transfer'])) {
                try {
                    $transfer = (new TransferModel())->setValueOf($_POST['valueOf'])
                        ->setFromAccountID($_SESSION['accountID'])
                        ->setToAccountID($_POST['toAccount'])
                        ->makeTransfer();
                    if (!$transfer) {
                        $this->errorAction(self::$INTERNAL_SERVER_ERROR_MESSAGE,
                            'Transfer failed.');
                        return;
                    }
                    $view = new View('transferMake');
                    echo $view->addData('transfer', $transfer)
                        ->render();
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
                try {
                    $accounts = (new AccountCollectionModel(null, null))->getObjects();
                    $view = new View('transferMake');
                    $view->addData('accounts', $accounts)
                        ->addData('username', $username);
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
