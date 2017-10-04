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
     * Checks that login details are valid.
     *
     * @param string $inputPassword The inputted password from user.
     *
     * @return $this AccountModel
     * @throws MySQLQueryException
     */
    public function checkLogin(string $inputPassword)
    {
        if (!$result = $this->db->query(
                "SELECT id FROM user_account WHERE username = '$this->_username';")) {
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
        if (!password_verify($this->_id.$inputPassword, $this->_password)) {
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
        if (!$result = $this->db->query("SELECT id, username, pwd FROM user_account WHERE id = $id;")) {
            throw new MySQLQueryException('Error in AccountModel::load');
        }
        $result = $result->fetch_assoc();
        if ($result['id'] == null) {
            return null;
        }
        $this->_id = $result['id'];
        $this->_username = $result['username'];
        $this->_password = $result['pwd'];
        return $this;
    }

    /**
     * Saves account information to the database
     *
     * @param string $password Inputted password.

     * @return $this AccountModel
     * @throws MySQLQueryException
     */
    public function save(string $password)
    {
        $name = $this->_username??"NULL";
        if (!isset($this->_id)) {
            // New account - Perform INSERT
            if (!$result = $this->db->query("INSERT INTO user_account VALUES (NULL,'$name','temp');")) {
                throw new MySQLQueryException('Query returns null from INSERT in AccountModel::save');
            }
            $this->_id = $this->db->insert_id;
            if (!$result = $this->db->query("UPDATE user_account SET pwd = '"
                .password_hash($this->_id.$password, PASSWORD_DEFAULT)."' WHERE id = $this->_id")) {
                throw new MySQLQueryException('Query returns null from UPDATE pwd in AccountModel::save');
            }
        } /*else {
            // saving existing account - perform UPDATE
            if (!$result = $this->db->query("UPDATE user_account SET name = '$name' WHERE id = $this->_id;")) {
                throw new MySQLQueryException('Query returns null from UPDATE name in AccountModel::save');
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
        if ($this->_username != 'admin') {
            if (!$result = $this->db->query("DELETE FROM user_account WHERE id = $this->_id;")) {
                throw new MySQLQueryException('Query returns null from AccountModel::delete');
            }
        }
        else {
            throw new MySQLQueryException('Cannot delete admin account');
        }

        return $this;
    }



}
