<?php
namespace cgwatkin\a2\model;

use cgwatkin\a2\exception\MySQLDatabaseException;
use mysqli;

/**
 * Class Model
 *
 * Connects to and configures the MySQL database with dummy data for testing.
 *
 * Base code provided by Andrew Gilman <a.gilman@massey.ac.nz>
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
            DB_PASS
        );

        if (!$this->db) {
            throw new MySQLDatabaseException($this->db->connect_error, $this->db->connect_errno);
        }

        //----------------------------------------------------------------------------
        // Creates the database and populates it with sample data
        $this->db->query("CREATE DATABASE IF NOT EXISTS ".DB_NAME.";");

        if (!$this->db->select_db(DB_NAME)) {
            throw new MySQLDatabaseException('MySQL database not available');
        }

        $result = $this->db->query("SHOW TABLES LIKE 'user_account';");
        if ($result->num_rows == 0) {
            // table doesn't exist
            // create it and populate with sample data

            $result = $this->db->query(
                                "CREATE TABLE user_account (
                                          id int(8) unsigned NOT NULL UNIQUE AUTO_INCREMENT,
                                          username varchar(256) NOT NULL UNIQUE,
                                          pwd varchar(256) NOT NULL,
                                          balance DECIMAL(19,4) NOT NULL,
                                          PRIMARY KEY (id) );"
            );
            if (!$result) {
                throw new MySQLDatabaseException('Failed creating table: user_account');
            }
            // Add sample data, password is hashed on combination of ID and inputted password
            if(!$this->db->query(
                "INSERT INTO user_account
                        VALUES (NULL,'admin','".password_hash('1'.'admin', PASSWORD_DEFAULT)."'),
                            (NULL,'Bob','".password_hash('2'.'bob', PASSWORD_DEFAULT)."'),
                            (NULL,'Mary','".password_hash('3'.'mary', PASSWORD_DEFAULT)."');"
            )) {
                throw new MySQLDatabaseException('Failed adding sample data to table: user_account');
            }
        }

        $result = $this->db->query("SHOW TABLES LIKE 'transaction';");
        if ($result->num_rows == 0) {
            // table doesn't exist
            // create it and populate with sample data

            $result = $this->db->query(
                "CREATE TABLE transaction (
                                          id int(8) unsigned NOT NULL UNIQUE AUTO_INCREMENT,
                                          datetimeOf DATETIME NOT NULL,
                                          valueOf DECIMAL(19,4) unsigned NOT NULL,
                                          fromAccount int(8) unsigned NOT NULL,
                                          toAccount int(8) unsigned NOT NULL,
                                          PRIMARY KEY (id),
                                          FOREIGN KEY (fromAccount) REFERENCES user_account(id),
                                          FOREIGN KEY (toAccount) REFERENCES user_account(id) );"
            );
            if (!$result) {
                throw new MySQLDatabaseException('Failed creating table: transaction');
            }
            // Add sample data, password is hashed on combination of ID and inputted password
            if(!$this->db->query(
                "INSERT INTO transaction
                        VALUES (NULL,".date("Y-m-d H:i:s").",20.00,1,2),
                        (NULL,".date("Y-m-d H:i:s").",5.00,2,3),
                        (NULL,".date("Y-m-d H:i:s").",8.00,2,1),
                        (NULL,".date("Y-m-d H:i:s").",2.00,3,1);"
            )) {
                throw new MySQLDatabaseException('Failed adding sample data to table: transaction');
            }
        }
        //----------------------------------------------------------------------------

    }
}
