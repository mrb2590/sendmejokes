<?php
/**
 * SendMeJokes (http://www.sendmejokes.com/)
 *
 * @author    Mike Buonomo <mike@sendmjokes.com>
 * @link      https://github.com/mrb2590/sendmejokes
 */

namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;

class JokeCategoriesTable
{
    /**
     * @var Zend\Db\TableGateway\TableGateway
     */
    protected $tableGateway;

    /**
     * @param Zend\Db\TableGateway\TableGateway $tableGateway
     */
    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    /**
     * @return Zend\Db\ResultSet\ResultSet 
     */
    public function fetchAll()
    {
        $resultSet = $this->tableGateway->select();
        return $resultSet;
    }

    /**
     * @param int(8)
     * @return Zend\Db\ResultSet\ResultSet
     */
    public function getJokeCategoriesByJokeId($joke_id)
    {
        $joke_id = (int) $joke_id;
        $resultSet = $this->tableGateway->select(array('joke_id' => $joke_id));
        $resultSet->buffer();
        return $resultSet;
    }

    /**
     * @param Application\Model\JokeCategory
     */
    public function addJokeCategory(JokeCategory $jokeCategory)
    {
        $data = array(
            'joke_id' => $jokeCategory->joke_id,
            'cat_id'  => $jokeCategory->cat_id,
        );

        $this->tableGateway->insert($data);
    }

    /**
     * @param  int(8)
     */
    public function deleteJokeCategoriesByJoke($joke_id)
    {
        $this->tableGateway->delete(array('joke_id' => (int) $joke_id));
    }

    /**
     * @param  int(8)
     */
    public function deleteJokeCategory($id)
    {
        $this->tableGateway->delete(array('id' => (int) $id));
    }
}