<?php
/*
 * Gwatkin, 15146508
 */
namespace cgwatkin\a2\model;

use cgwatkin\a2\exception\MySQLQueryException;

/**
 * Class TransferCollectionModel
 *
 * Base code provided by Andrew Gilman <a.gilman@massey.ac.nz>
 *
 * @package cgwatkin/a2
 * @author  Cai Gwatkin <caigwatkin@gmail.com>
 */
class TransferCollectionModel extends CollectionModel
{
    /**
     * TransferCollectionModel constructor.
     *
     * Sends 'transfer' as table to parent constructor.
     *
     * @param int $limit Limit of number of rows to be returned.
     * @param int $offset Offset from zero'th row.
     * @param int $accountID ID of account for transactions to be loaded from.
     *
     * @throws MySQLQueryException
     */
    function __construct($accountID, $limit = null, $offset = null)
    {
        $table = 'transfer';
        $limitClause = $limit ? "LIMIT $limit" : null;
        $offsetClause = $offset ? "OFFSET $offset" : null;
        $orderClause = 'ORDER BY datetimeOf DESC';
        $whereClause = "WHERE fromAccount = $accountID OR toAccount = $accountID";
        try {
            parent::__construct(TransferModel::class,$table, $limitClause, $offsetClause,
                $orderClause, $whereClause);
        }
        catch (MySQLQueryException $ex) {
            throw $ex;
        }
    }
}
