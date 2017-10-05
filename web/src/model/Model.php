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
                error_log($this->db->error);
                throw new MySQLDatabaseException('Failed creating table: user_account');
            }
            // Add sample data, password is hashed on combination of ID and inputted password
            $pwd1 = password_hash('1'.'admin', PASSWORD_DEFAULT);
            $pwd2 = password_hash('2'.'bob', PASSWORD_DEFAULT);
            $pwd3 = password_hash('3'.'mary', PASSWORD_DEFAULT);
            $pwd4 = password_hash('3'.'joe', PASSWORD_DEFAULT);
            if(!$this->db->query(
                "INSERT INTO user_account
                        VALUES (NULL,'admin','$pwd1','0.00'),
                            (NULL,'Bob','$pwd2','10.00'),
                            (NULL,'Mary','$pwd3','10.00'),
                            (NULL,'Joe','$pwd4','10.00');"
            )) {
                error_log($this->db->error);
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
                error_log($this->db->error);
                throw new MySQLDatabaseException('Failed creating table: transaction');
            }
            // Add sample data, password is hashed on combination of ID and inputted password
            $date1 = date("Y-m-d H:i:s");
            $date2 = date("Y-m-d H:i:s");
            $date3 = date("Y-m-d H:i:s");
            $date4 = date("Y-m-d H:i:s");
            if(!$this->db->query(
                "INSERT INTO transaction
                        VALUES (NULL,'$date1',20.00,2,3),
                        (NULL,'$date2',5.00,3,4),
                        (NULL,'$date3',8.00,3,2),
                        (NULL,'$date4',2.00,4,2);"
            )) {
                error_log($this->db->error);
                throw new MySQLDatabaseException('Failed adding sample data to table: transaction');
            }
        }
        //----------------------------------------------------------------------------

    }
}
