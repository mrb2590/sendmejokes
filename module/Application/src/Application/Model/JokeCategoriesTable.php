<?php
namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;

class JokeCategoriesTable
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

    public function getJokeCategories(User $user)
    {
        $id = (int) $user->user_id;
        $resultSet = $this->tableGateway->select(array('joke_id' => $id));
        return $resultSet;
    }

    public function addJokeCategory(JokeCategory $jokeCategory)
    {
        $data = array(
            'joke_id' => $jokeCategory->joke_id,
            'cat_id'  => $jokeCategory->cat_id,
        );

        $this->tableGateway->insert($data);
    }

    public function deleteJokeCategory($id)
    {
        $this->tableGateway->delete(array('id' => (int) $id));
    }
}