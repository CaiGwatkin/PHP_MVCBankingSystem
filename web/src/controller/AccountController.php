<?php
namespace cgwatkin\a2\controller;

use cgwatkin\a2\model\AccountModel;
use cgwatkin\a2\model\AccountCollectionModel;
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
     */
    public function indexAction() 
    {
        $collection = new AccountCollectionModel();
        $accounts = $collection->getAccounts();
        $view = new View('accountIndex');
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