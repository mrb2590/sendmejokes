<?php
namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;

class JokeTable
{
    protected $tableGateway;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function getJokesByCategory($cat_id)
    {
        $resultSet = $this->tableGateway->select(function (Select $select) {
            // Select columns and count the forums.
            $select->columns(array(
                'category_name',
                'forumsCount' => new Expression('COUNT(forums.forum_id)')
            ));
            // Left-join with the forums table.
            $select->join('forums', 'categories.category_id = forums.category_id', array(), 'left');
            // Group by the category name.
            $select->group('categories.category_name');
        });
        return $resultSet;
    }
    
    public function fetchAll()
    {
        $resultSet = $this->tableGateway->select();
        return $resultSet;
    }

    public function fetchPaginated($limit, $page)
    {
        $limit = (int) $limit;
        $page = (int) $page;
        $offset = $limit * ($page - 1);
        $resultSet = $this->tableGateway->select(function (Select $select) use ($limit, $offset) {
            $select->limit($limit)->offset($offset);
        });
        return $resultSet;
    }

    public function getJoke($joke_id)
    {
        $joke_id  = (int) $joke_id;
        $rowset = $this->tableGateway->select(array('joke_id' => $joke_id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find joke");
        }
        return $row;
    }

    public function deleteJoke($id)
    {
        $this->tableGateway->delete(array('id' => (int) $id));
    }
}