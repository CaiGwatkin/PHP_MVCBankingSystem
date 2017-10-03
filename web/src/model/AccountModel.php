<?php
namespace cgwatkin\a2\model;


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
     * @var string Account Username
     */
    private $_username;

    /**
     * @var string Account password
     */
    private $_unformedPassword;


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
    public function getUsername()
    {
        return $this->_username;
    }

    /**
     * @param string $_username Account name
     *
     * @return $this AccountModel
     */
    public function setUsername(string $_username)
    {
        $this->_username = $_username;

        return $this;
    }

    /**
     * @param string $_password Account password
     *
     * @return $this AccountModel
     */
    public function setUnformedPassword(string $_password)
    {
        $this->_unformedPassword = $_password;

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
        $this->_username = $result['username'];
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
        $name = $this->_username??"NULL";
        if (!isset($this->_id)) {
            // New account - Perform INSERT
            if (!$result = $this->db->query("INSERT INTO user_account VALUES (NULL,'$name','"
                .password_hash($this->_unformedPassword, PASSWORD_DEFAULT)."');")) {
                throw new MySQLQueryException('Query returns null from INSERT in AccountModel::save');
            }
            $this->_id = $this->db->insert_id;
            if (!$result = $this->db->query("UPDATE user_account SET pwd = '"
                .password_hash($this->_id.$this->_unformedPassword, PASSWORD_DEFAULT)."' WHERE id = $this->_id")) {
                throw new MySQLQueryException('Query returns null from UPDATE pwd in AccountModel::save');
            }
        } /*else {
            // saving existing account - perform UPDATE
            if (!$result = $this->db->query("UPDATE user_account SET name = '$name' WHERE id = $this->_id;")) {
//                throw new MySQLQueryException('Query returns null from UPDATE name in AccountModel::save');
            }

        }*/

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
