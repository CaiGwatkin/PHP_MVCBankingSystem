<?php
namespace cgwatkin\a2\model;

/**
 * Class AccountCollectionModel
 *
 * Base code provided by Andrew Gilman <a.gilman@massey.ac.nz>
 *
 * @package cgwatkin/a2
 * @author  Cai Gwatkin <caigwatkin@gmail.com>
 */
class AccountCollectionModel extends Model
{
    private $_accountIds;

    private $_N;

    function __construct()
    {
        parent::__construct();
        if (!$result = $this->db->query("SELECT id FROM user_account ORDER BY id;")) {
            throw new MySQLQueryException('Query returns null from AccountCollectionModel::__construct');
        }
        $this->_accountIds = array_column($result->fetch_all(), 0);
        $this->_N = $result->num_rows;
    }

    /**
     * Get account collection
     *
     * @return Generator|AccountModel[] Accounts
     */
    public function getAccounts() 
    {
        foreach ($this->_accountIds as $id) {
            // Use a generator to save on memory/resources
            // load accounts from DB one at a time only when required
            yield (new AccountModel())->load($id);
        }
    }


}
