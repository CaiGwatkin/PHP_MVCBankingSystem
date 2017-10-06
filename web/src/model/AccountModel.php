<?php
namespace cgwatkin\a2\model;

use cgwatkin\a2\exception\MySQLQueryException;


/**
 * Class AccountModel
 *
 * Base code provided by Andrew Gilman <a.gilman@massey.ac.nz>
 *
 * @package cgwatkin/a2
 * @author  Cai Gwatkin <caigwatkin@gmail.com>
 */
class AccountModel extends Model
{
    /**
     * @var integer Account ID.
     */
    private $_id;
    
    /**
     * @var string Account username.
     */
    private $_username;
    
    /**
     * @var string Account password.
     */
    private $_password;
    
    /**
     * @var string Account balance.
     */
    private $_balance;
    
    /**
     * @return int Account ID
     */
    public function getID()
    {
        return $this->_id;
    }
    
    /**
     * @param int $id Account ID.
     * @return AccountModel $this
     */
    private function setID(int $id)
    {
        $this->_id = $id;
        return $this;
    }

    /**
     * @return string Account Name
     */
    public function getUsername()
    {
        return $this->_username;
    }

    /**
     * @param string $_username Account name
     *
     * @return AccountModel $this
     */
    public function setUsername(string $_username)
    {
        $this->_username = $_username;

        return $this;
    }
    
    /**
     * @param string $password Account password
     * @return AccountModel $this
     */
    public function setPassword(string $password)
    {
        $this->_password = $password;
        return $this;
    }
    
    /**
     * @return float Account balance.
     */
    public function getBalance()
    {
        return $this->_balance;
    }
    
    /**
     * @param float $balance Account balance.
     * @return AccountModel $this
     */
    private function setBalance(float $balance)
    {
        $this->_balance = $balance;
        return $this;
    }
    
    /**
     * Checks that login details are valid.
     *
     * @param string $username The username for the account to be logged in.
     * @param string $password The password.
     *
     * @return $this AccountModel
     * @throws MySQLQueryException
     */
    public function checkLogin(string $username, string $password)
    {
        if (!$result = $this->db->query(
            "SELECT id
            FROM user_account
            WHERE username = '$username';"
        )) {
            throw new MySQLQueryException('Error in AccountModel::checkLogin');
        }
        $result = $result->fetch_assoc();
        try {
            $this->load($result['id']);
        }
        catch (MySQLQueryException $ex) {
            error_log($ex->getMessage());
            return null;
        }
        if (!password_verify($this->_id.$password, $this->_password)) {
            return null;
        }
        else {
            return $this;
        }
    }

    /**
     * Loads account information from the database
     *
     * @param int $id Account ID
     *
     * @return $this AccountModel
     * @throws MySQLQueryException
     */
    public function load($id)
    {
        if (!$result = $this->db->query(
            "SELECT id, username, pwd, balance
            FROM user_account
            WHERE id = $id;"
        )) {
            throw new MySQLQueryException('Error in AccountModel::load');
        }
        if ($result->num_rows == 0) {
            throw new MySQLQueryException('No account found with id '.$id.' in AccountModel::load');
        }
        $result = $result->fetch_assoc();
        return $this->setID($result['id'])
            ->setUsername($result['username'])
            ->setPassword($result['pwd'])
            ->setBalance($result['balance']);
    }

    /**
     * Saves account information to the database
     *
     * Should only be called after account model object's username and password has been set.

     * @return $this AccountModel
     * @throws MySQLQueryException
     */
    public function save()
    {
        if (!isset($this->_id)) {
            if (!$result = $this->db->query(
                "INSERT INTO user_account
                VALUES (
                    NULL,
                    '$this->_username',
                    'temp',
                    0.00
                );"
            )) {
                throw new MySQLQueryException('Error from "INSERT INTO user_account" in AccountModel::save');
            }
            $id = $this->db->insert_id;
            if (!$result = $this->db->query(
                "UPDATE user_account
                SET pwd = '".password_hash($this->_id.$this->_password, PASSWORD_DEFAULT)."'
                WHERE id = $id"
            )) {
                throw new MySQLQueryException('Error from "UPDATE user_account SET" pwd in AccountModel::save');
            }
            try {
                return $this->load($id);
            }
            catch (MySQLQueryException $ex) {
                throw $ex;
            }
        }
    }

    /**
     * Deletes account from the database

     * @return $this AccountModel
     * @throws MySQLQueryException
     */
    public function delete()
    {
        if ($this->_username != 'admin') {
            if (!$result = $this->db->query(
                "DELETE FROM user_account
                WHERE id = $this->_id;"
            )) {
                throw new MySQLQueryException('Error from DELETE in AccountModel::delete');
            }
        }
        else {
            throw new MySQLQueryException('Cannot delete admin account');
        }

        return $this;
    }

    private function updateBalance() {
        if (!$result = $this->db->query(
            "UPDATE user_account
            SET balance = $this->_balance
            WHERE id = $this->_id;"
        )) {
            throw new MySQLQueryException('Error from UPDATE in AccountModel::addToBalance');
        }
    }

    public function addToBalance($amount) {
        $this->_balance += $amount;
        try {
            $this->updateBalance();
        }
        catch (MySQLQueryException $ex) {
            throw $ex;
        }
    }

    public function subtractFromBalance($amount) {
        $this->_balance -= $amount;
        try {
            $this->updateBalance();
        }
        catch (MySQLQueryException $ex) {
            throw $ex;
        }
    }
}
