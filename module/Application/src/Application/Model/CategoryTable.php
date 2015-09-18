<?php
namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;

class CategoryTable
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

    public function getCategory($cat_id)
    {
        $cat_id  = (int) $cat_id;
        $rowset = $this->tableGateway->select(array('cat_id' => $cat_id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Category not found");
        }
        return $row;
    }

    public function deleteCategory($cat_id)
    {
        $this->tableGateway->delete(array('cat_id' => (int) $cat_id));
    }
}