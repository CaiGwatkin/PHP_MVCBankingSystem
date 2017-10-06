<?php
namespace cgwatkin\a2\model;

use cgwatkin\a2\exception\MySQLQueryException;


/**
 * Class TransferModel
 *
 * Base code provided by Andrew Gilman <a.gilman@massey.ac.nz>
 *
 * @package cgwatkin/a2
 * @author  Cai Gwatkin <caigwatkin@gmail.com>
 */
class TransferModel extends Model
{
    /**
     * @var int Transaction ID.
     */
    private $_id;
    
    /**
     * @var string Transaction datetime.
     */
    private $_dateTimeOf;
    
    /**
     * @var float Value of transaction.
     */
    private $_valueOf;
    
    /**
     * @var int The ID of the account being transferred from.
     */
    private $_fromAccountID;
    
    /**
     * @var int The ID of the account being transferred to.
     */
    private $_toAccountID;
    
    /**
     * @return int $id Transaction ID.
     */
    public function getID()
    {
        return $this->_id;
    }
    
    /**
     * @param int $id Transaction ID.
     * @return TransferModel $this
     */
    private function setID(int $id)
    {
        $this->_id = $id;
        return $this;
    }
    
    /**
     * @return string $dateTime Transaction datetime.
     */
    public function getDateTimeOf()
    {
        return $this->_dateTimeOf;
    }
    
    /**
     * @param string $datetime Transaction datetime.
     * @return TransferModel $this
     */
    private function setDateTimeOf(string $datetime)
    {
        $this->_dateTimeOf = $datetime;
        return $this;
    }
    
    /**
     * @return float $_valueOf Value of transaction.
     */
    public function getValueOf()
    {
        return $this->_valueOf;
    }
    
    /**
     * @param float $value Value of transaction.
     * @return TransferModel $this
     */
    public function setValueOf(float $value)
    {
        $this->_valueOf = $value;
        return $this;
    }
    
    /**
     * @return int $_fromAccountID The ID of the account being transferred from.
     */
    public function getFromAccountID()
    {
        return $this->_fromAccountID;
    }
    
    /**
     * @param int $id The ID of the account being transferred from.
     * @return TransferModel $this
     */
    public function setFromAccountID(int $id)
    {
        $this->_fromAccountID = $id;
        return $this;
    }
    
    /**
     * @return int $_toAccountID The ID of the account being transferred to.
     */
    public function getToAccountID()
    {
        return $this->_toAccountID;
    }
    
    /**
     * @param int $id The ID of the account being transferred to.
     * @return TransferModel $this
     */
    public function setToAccountID(int $id)
    {
        $this->_toAccountID = $id;
        return $this;
    }
    
    /**
     * Loads transaction model from MySQL.
     *
     * @param int $id Transaction ID.
     * @return TransferModel $this
     * @throws MySQLQueryException $ex Exception generated from failed MySQL query operation.
     */
    public function load(int $id)
    {
        if (!$result = $this->db->query(
            "SELECT id, datetimeOf, valueOf, fromAccount, toAccount
            FROM transfer
            WHERE id = $id;"
        )) {
            error_log($this->db->error);
            throw new MySQLQueryException('Error in SELECT from TransferModel::load');
        }
        if ($result->num_rows == 0) {
            return null;
        }
        $result = $result->fetch_assoc();
        return $this->setId($result['id'])
            ->setDateTimeOf($result['datetimeOf'])
            ->setValueOf($result['valueOf'])
            ->setFromAccountID($result['fromAccount'])
            ->setToAccountID($result['toAccount']);
    }
    
    /**
     * Saves this transaction model to MySQL database.
     *
     * @return TransferModel $this
     * @throws MySQLQueryException $ex Exception generated from failed MySQL query operation.
     */
    public function save()
    {
        if (!isset($this->_id)) {
            $date = date("Y-m-d H:i:s");
            if (!$result = $this->db->query(
                "INSERT INTO transfer
                VALUES (
                    NULL,
                    '$date',
                    $this->_valueOf,
                    $this->_fromAccountID,
                    $this->_toAccountID
                );"
            )) {
                throw new MySQLQueryException('Error from "INSERT INTO transfer" in TransferModel::save');
            }
            $id = $this->db->insert_id;
            try {
                return $this->load($id);
            }
            catch (MySQLQueryException $ex) {
                throw $ex;
            }
        }
        return $this;
    }
    
    /**
     * Makes a new transaction model.
     *
     * @return TransferModel $this
     * @throws MySQLQueryException $ex Exception generated from failed MySQL query operation.
     */
    public function makeTransfer()
    {
        try {
            error_log('here1');
            $this->save();
            $this->updateAccountBalance();
        }
        catch (MySQLQueryException $ex) {
            throw $ex;
        }
        return $this;
    }

    /**
     * Updates account balances after transaction made.
     *
     * @throws MySQLQueryException
     */
    private function updateAccountBalance() {
        try {
            error_log('here');
            (new AccountModel())->load($this->_toAccountID)
                ->addToBalance($this->_valueOf);
            (new AccountModel())->load($this->_fromAccountID)
                ->subtractFromBalance($this->_valueOf);
        }
        catch (MySQLQueryException $ex) {
            throw $ex;
        }
    }
}
