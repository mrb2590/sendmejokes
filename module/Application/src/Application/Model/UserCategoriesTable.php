<?php
namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;

class UserCategoriesTable
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
        $id = (int) $user->user_id;
        $resultSet = $this->tableGateway->select(array('user_id' => $id));
        return $resultSet;
    }

    public function addUserCategory(UserCategory $userCategory)
    {
        $data = array(
            'user_id' => $userCategory->user_id,
            'cat_id'  => $userCategory->cat_id,
        );

        $this->tableGateway->insert($data);
    }

    public function deleteCategory($id)
    {
        $this->tableGateway->delete(array('id' => (int) $id));
    }
}