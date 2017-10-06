<?php
namespace cgwatkin\a2\model;

use cgwatkin\a2\exception\MySQLQueryException;

/**
 * Abstract Class CollectionModel
 *
 * Base code provided by Andrew Gilman <a.gilman@massey.ac.nz>
 *
 * @package cgwatkin/a2
 * @author  Cai Gwatkin <caigwatkin@gmail.com>
 */
class CollectionModel extends Model
{
    /**
     * @var array IDs.
     */
    private $_ids;

    /**
     * @var callable Class for collection.
     */
    private $_class;

    /**
     * @var int Number of models in collection.
     */
    private $_num;

    /**
     * CollectionModel constructor.
     *
     * @param callable $class The class to be generated as a collection.
     * @param string $table The table to gather collection from.
     * @param int $limit Limit of number of rows to be returned.
     * @param int $offset Offset from zero'th row.
     *
     * @throws MySQLQueryException
     */
    function __construct($class, string $table, int $limit = null, int $offset = null,
                         string $order = null, string $whereClause = null)
    {
        parent::__construct();
        $this->loadIDs($table, $limit, $offset, $order, $whereClause);
        $this->_class = $class;
    }

    private function loadIDs(string $table, $limit, $offset,
                             $order, $whereClause) {
        if (!$result = $this->db->query(
            "SELECT id
            FROM $table
            $whereClause
            ORDER BY $order
            LIMIT $limit
            OFFSET $offset;"
        )) {
            throw new MySQLQueryException('Error from SELECT in CollectionModel::__construct');
        }
        $this->_ids = array_column($result->fetch_all(), 0);
        $this->_num = $result->num_rows;
    }

    /**
     * @return int Number of models in collection.
     */
    public function getNum()
    {
        return $this->_num;
    }

    /**
     * Get collection of models
     *
     * @return Generator|Model[] model objects
     */
    public function getObjects()
    {
        foreach ($this->_ids as $id) {
            yield (new $this->_class())->load($id);
        }
    }
}
