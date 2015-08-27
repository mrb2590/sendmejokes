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

    public function getJokeCategories(Joke $joke)
    {
        $resultSet = $this->tableGateway->select(array('joke_id' => $joke->joke_id));
        return $resultSet;
    }
}