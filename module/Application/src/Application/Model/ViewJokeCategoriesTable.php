<?php
namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;

class ViewJokeCategoriesTable
{
    protected $tableGateway;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function fetchAll()
    {
        $resultSet = $this->tableGateway->select();
        $resultSet->buffer();
        return $resultSet;
    }

    public function fetchPaginatedByCategory($cat_id, $limit, $page)
    {
        $cat_id = (int) $cat_id;
        $limit = (int) $limit;
        $page = (int) $page;
        $offset = $limit * ($page - 1);
        $resultSet = $this->tableGateway->select(function (Select $select) use ($cat_id, $limit, $offset) {
            $select->where('cat_id = ' . $cat_id)->limit($limit)->offset($offset);
        });
        return $resultSet;
    }

    public function getJokesByCategory($cat_id)
    {
        $resultSet = $this->tableGateway->select(array('cat_id' => $cat_id));
        return $resultSet; 
    }
}