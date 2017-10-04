<?php
namespace cgwatkin\a2\model;

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
                                          PRIMARY KEY (id) );"
            );
            if (!$result) {
                throw new MySQLDatabaseException('Unable to create table user_account');
                error_log("Failed creating table account",0);
            }
            // Add sample data, password is hashed on combination of ID and inputted password
            if(!$this->db->query(
                "INSERT INTO user_account
                        VALUES (NULL,'admin','".password_hash('1'.'admin', PASSWORD_DEFAULT)."'),
                            (NULL,'Bob','".password_hash('2'.'bob', PASSWORD_DEFAULT)."'),
                            (NULL,'Mary','".password_hash('3'.'mary', PASSWORD_DEFAULT)."');"
            )) {
                // TODO throw exception
                error_log("Failed creating sample data!",0);
            }
        }

        $result = $this->db->query("SHOW TABLES LIKE 'transaction';");
        if ($result->num_rows == 0) {
            // table doesn't exist
            // create it and populate with sample data

            $result = $this->db->query(
                "CREATE TABLE transaction (
                                          id int(8) unsigned NOT NULL UNIQUE AUTO_INCREMENT,
                                          dateOf DATE NOT NULL,
                                          typeOf CHAR NOT NULL,
                                          valueOf DECIMAL(19,4) unsigned NOT NULL,
                                          PRIMARY KEY (id) );"
            );
            if (!$result) {
                // TODO throw exception
                error_log("Failed creating table account",0);
            }
            // Add sample data, password is hashed on combination of ID and inputted password
            if(!$this->db->query(
                "INSERT INTO user_account
                        VALUES (NULL,'admin','".password_hash('1'.'admin', PASSWORD_DEFAULT)."'),
                            (NULL,'Bob','".password_hash('2'.'bob', PASSWORD_DEFAULT)."'),
                            (NULL,'Mary','".password_hash('3'.'mary', PASSWORD_DEFAULT)."');"
            )) {
                // TODO throw exception
                error_log("Failed creating sample data!",0);
            }
        }
        //----------------------------------------------------------------------------

    }
}
