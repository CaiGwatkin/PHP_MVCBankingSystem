<?php
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
    function __construct(int $limit, int $offset, int $accountID)
    {
        try {
            parent::__construct(TransferModel::class,'transfer', $limit, $offset, 'datetimeOf',
                "WHERE fromAccount = $accountID OR toAccount = $accountID");
        }
        catch (MySQLQueryException $ex) {
            throw $ex;
        }
    }
}
