<?php
/*
 * Gwatkin, 15146508
 */
namespace cgwatkin\a2\model;

use cgwatkin\a2\exception\MySQLQueryException;

/**
 * Class AccountCollectionModel
 *
 * Base code provided by Andrew Gilman <a.gilman@massey.ac.nz>
 *
 * @package cgwatkin/a2
 * @author  Cai Gwatkin <caigwatkin@gmail.com>
 */
class AccountCollectionModel extends CollectionModel
{
    /**
     * TransferCollectionModel constructor.
     *
     * Sends 'transfer' as table to parent constructor.
     *
     * @param int $limit Limit of number of rows to be returned.
     * @param int $offset Offset from zero'th row.
     *
     * @throws MySQLQueryException
     */
    function __construct($limit = null, $offset = null)
    {
        $table = 'user_account';
        $limitClause = $limit ? "LIMIT $limit" : null;
        $offsetClause = $offset ? "OFFSET $offset" : null;
        $orderClause = 'ORDER BY id ASC';
        try {
            parent::__construct(AccountModel::class, $table, $limitClause, $offsetClause, $orderClause);
        }
        catch (MySQLQueryException $ex) {
            throw $ex;
        }
    }
}
