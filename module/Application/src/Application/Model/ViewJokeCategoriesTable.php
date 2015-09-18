<?php
namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;

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

    public function getJokesByCategory($cat_id)
    {
        $resultSet = $this->tableGateway->select(array('cat_id' => $cat_id));
        return $resultSet; 
    }
}