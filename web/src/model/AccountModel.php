<?php
namespace cgwatkin\a2\model;


/**
 * Class AccountModel
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
     * Checks that login details are valid.
     *
     * @param string $username An account username.
     * @param string $password The password.
     *
     * @return $id ID of account, if valid.
     */
    public function checkLogin(string $username, string $password)
    {
        if (!$result = $this->db->query("SELECT `id`, `password` FROM `account` WHERE `username` = $username;")) {
            return null;
        }
        $result = $result->fetch_assoc();
        if (!password_verify($password, $result['password'])) {
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
     */
    public function load($id)
    {
        if (!$result = $this->db->query("SELECT * FROM `account` WHERE `id` = $id;")) {
            // throw new ...
        }

        $result = $result->fetch_assoc();
        $this->_name = $result['name'];
        $this->_id = $id;

        return $this;
    }

    /**
     * Saves account information to the database

     * @return $this AccountModel
     */
    public function save()
    {
        $name = $this->_name??"NULL";
        if (!isset($this->_id)) {
            // New account - Perform INSERT
            if (!$result = $this->db->query("INSERT INTO `account` VALUES (NULL,'$name');")) {
                // throw new ...
            }
            $this->_id = $this->db->insert_id;
        } else {
            // saving existing account - perform UPDATE
            if (!$result = $this->db->query("UPDATE `account` SET `name` = '$name' WHERE `id` = $this->_id;")) {
                // throw new ...
            }

        }

        return $this;
    }

    /**
     * Deletes account from the database

     * @return $this AccountModel
     */
    public function delete()
    {
        if (!$result = $this->db->query("DELETE FROM `account` WHERE `account`.`id` = $this->_id;")) {
            //throw new ...
        }

        return $this;
    }



}