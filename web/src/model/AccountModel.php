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
     * @var integer Account ID
     */
    private $_id;
    /**
     * @var string Account Name
     */
    private $_name;

    /**
     * @var string Account password
     */
    private $_password;


    /**
     * @return int Account ID
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * @return string Account Name
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * @param string $_name Account name
     *
     * @return $this AccountModel
     */
    public function setName(string $_name)
    {
        $this->_name = $_name;

        return $this;
    }

    /**
     * @param string $_password Account password
     *
     * @return $this AccountModel
     */
    public function setPassword(string $_password)
    {
        $this->_password = $_password;

        return $this;
    }
    
    /**
     * Checks that login details are valid.
     *
     * @param string $username An account username.
     * @param string $password The password.
     *
     * @return $id ID of account, if valid.
     */
    public function checkLogin(string $username, string $password)
    {
        if (!$result = $this->db->query(
                "SELECT id, pwd FROM user_account WHERE username = '$username';")) {
            return null;
        }
        $result = $result->fetch_assoc();
        if (!password_verify($result['id'].$password, $result['pwd'])) {
            return null;
        }
        else {
            return $result['id'];
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
        if (!$result = $this->db->query("SELECT id, username FROM user_account WHERE id = $id;")) {
//            throw new MySQLQueryException('Query returns null from AccountModel::load');
        }
        $result = $result->fetch_assoc();
        $this->_name = $result['username'];
        $this->_id = $id;
        return $this;
    }

    /**
     * Saves account information to the database

     * @return $this AccountModel
     * @throws MySQLQueryException
     */
    public function save()
    {
        $name = $this->_name??"NULL";
        if (!isset($this->_id)) {
            // New account - Perform INSERT
            if (!$result = $this->db->query("INSERT INTO user_account VALUES (NULL,'$name','"
                .password_hash('test', PASSWORD_DEFAULT)."');")) {
                //TODO make work
//                throw new MySQLQueryException('Query returns null from INSERT in AccountModel::save');
            }
            $this->_id = $this->db->insert_id;
        } else {
            // saving existing account - perform UPDATE
            if (!$result = $this->db->query("UPDATE user_account SET name = '$name' WHERE id = $this->_id;")) {
//                throw new MySQLQueryException('Query returns null from UPDATE in AccountModel::save');
            }

        }

        return $this;
    }

    /**
     * Deletes account from the database

     * @return $this AccountModel
     * @throws MySQLQueryException
     */
    public function delete()
    {
        if (!$result = $this->db->query("DELETE FROM user_account WHERE user_account.id = $this->_id;")) {
//            throw new MySQLQueryException('Query returns null from AccountModel::delete');
        }

        return $this;
    }



}
