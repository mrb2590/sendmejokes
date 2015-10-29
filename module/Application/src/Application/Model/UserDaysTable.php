<?php
/**
 * SendMeJokes (http://www.sendmejokes.com/)
 *
 * @author    Mike Buonomo <mike@sendmjokes.com>
 * @link      https://github.com/mrb2590/sendmejokes
 */

namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;

class UserDaysTable
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
        $resultSet->buffer();
        return $resultSet;
    }

    /**
     * @param Application\Model\User
     * @return Zend\Db\ResultSet\ResultSet 
     */
    public function getUserDays(User $user)
    {
        $resultSet = $this->tableGateway->select(array('user_id' => $user->user_id));
        $resultSet->buffer();
        return $resultSet;
    }

    /**
     * @param Application\Model\UserDay
     */
    public function addUserDay(UserDay $userDay)
    {
        $data = array(
            'user_id' => $userDay->user_id,
            'day'     => $userDay->day,
        );

        $this->tableGateway->insert($data);
    }

    /**
     * @param int(8) 
     */
    public function deleteUserDaysByUserId($user_id)
    {
        $this->tableGateway->delete(array('user_id' => (int) $user_id));
    }
}