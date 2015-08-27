<?php
namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;

class JokeTable
{
    protected $tableGateway;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function fetchAll()
    {
        $resultSet = $this->tableGateway->select();
        return $resultSet;
    }

    public function getJoke($id)
    {
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('cat_id' => $id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }

    public function deleteJoke($id)
    {
        $this->tableGateway->delete(array('id' => (int) $id));
    }
}