<?php
/**
 * SendMeJokes (http://www.sendmejokes.com/)
 *
 * @author    Mike Buonomo <mike@sendmjokes.com>
 * @link      https://github.com/mrb2590/sendmejokes
 */

namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;
use Application\Model\UserSentJoke;

class UserSentJokesTable
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
    public function getUserSentJokes($user)
    {
        $resultSet = $this->tableGateway->select(array('user_id' => $user->user_id));
        $resultSet->buffer();
        return $resultSet;
    }

    /**
     * @return Zend\Db\ResultSet\ResultSet 
     */
    public function fetchAll()
    {
        $resultSet = $this->tableGateway->select();
        $resultSet->buffer();
        return $resultSet;
    }

    /**
     * @param Application\Model\UserSentJoke
     */
    public function addUserSentJoke(UserSentJoke $userSentJoke)
    {
        $data = array(
            'joke_id' => $userSentJoke->joke_id,
            'user_id'  => $userSentJoke->user_id,
        );

        $this->tableGateway->insert($data);
    }
}