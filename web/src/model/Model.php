<?php
namespace cgwatkin\a2\model;

use cgwatkin\a2\NoMySQLException;
use mysqli;

/**
 * Class Model
 *
 * Connects to and configures the MySQL database with dummy data for testing.
 *
 * @package cgwatkin/a2
 * @author  Cai Gwatkin <caigwatkin@gmail.com>
 */
class Model
{
    protected $db;

    function __construct()
    {
        $this->db = new mysqli(
            DB_HOST,
            DB_USER,
            DB_PASS/*,
            DB_NAME*/
        );

        if (!$this->db) {
            throw new NoMySQLException($this->db->connect_error, $this->db->connect_errno);
        }

        //----------------------------------------------------------------------------
        // Creates the database and populates it with sample data
        $this->db->query("CREATE DATABASE IF NOT EXISTS ".DB_NAME.";");

        if (!$this->db->select_db(DB_NAME)) {
            throw new NoMySQLException("Mysql database not available!", 0);
        }

        $result = $this->db->query("SHOW TABLES LIKE 'user_account';");

        if ($result->num_rows == 0) {
            // table doesn't exist
            // create it and populate with sample data

            $result = $this->db->query(
                                "CREATE TABLE user_account (
                                          id int(8) unsigned NOT NULL AUTO_INCREMENT,
                                          username varchar(256) NOT NULL UNIQUE,
                                          pwd varchar(256) DEFAULT NULL,
                                          PRIMARY KEY (id) );"
            );
            if (!$result) {
                // handle appropriately
                error_log("Failed creating table account",0);
            }
            if(!$this->db->query(
                "INSERT INTO user_account
                        VALUES (NULL,'admin','".password_hash('admin', PASSWORD_DEFAULT)."'),
                            (NULL,'Bob','".password_hash('bob', PASSWORD_DEFAULT)."'),
                            (NULL,'Mary','".password_hash('mary', PASSWORD_DEFAULT)."');"
            )) {
                // handle appropriately
                error_log("Failed creating sample data!",0);
            }
        }
        //----------------------------------------------------------------------------

    }
}
