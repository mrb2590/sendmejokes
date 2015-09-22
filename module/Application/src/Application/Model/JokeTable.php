<?php
/**
 * SendMeJokes (http://www.sendmejokes.com/)
 *
 * @author    Mike Buonomo <mike@sendmjokes.com>
 * @link      https://github.com/mrb2590/sendmejokes
 */

namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;

class JokeTable
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
     * @param int(8) 
     * @return Zend\Db\ResultSet\ResultSet 
     */
    public function getJokesByCategory($cat_id)
    {
        $cat_id = (int) $cat_id;

        $resultSet = $this->tableGateway->select(function (Select $select) {
            $select->join(array('jc' => 'joke_categories'), array('joke.joke_id = jc.joke_id'));
        });

        return $resultSet;
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
     * @param integer $limit
     * @param integer $page
     * @return Zend\Db\ResultSet\ResultSet 
     */
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

    /**
     * @param int(8)
     * @return Application\Model\Joke 
     */
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

    /**
     * @param Application\Model\Joke
     * @return Application\Model\Joke
     */
    public function addJoke(Joke $joke)
    {
        if (!isset($joke->joke) || $joke->joke == '') {
            throw new \Exception("Joke cannot be blank");
        }
        $data = array(
            'joke'   => $joke->joke,
            'answer' => $joke->answer,
        );
        $this->tableGateway->insert($data);
        $rowset = $this->tableGateway->select(array('joke' => $joke->joke));
        return $rowset->current();
    }

    /**
     * @param int(8)
     */
    public function deleteJoke($joke_id)
    {
        $this->tableGateway->delete(array('joke_id' => (int) $joke_id));
    }
}