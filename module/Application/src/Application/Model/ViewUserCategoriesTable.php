<?php
namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;

class ViewUserCategoriesTable
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

    public function getUserCategories(User $user)
    {
        $resultSet = $this->tableGateway->select(array('email' => $user->email));
        return $resultSet;
    }
}