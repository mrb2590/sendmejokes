<?php
/**
 * SendMeJokes (http://www.sendmejokes.com/)
 *
 * @author    Mike Buonomo <mike@sendmjokes.com>
 * @link      https://github.com/mrb2590/sendmejokes
 */

namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Crypt\Password\Bcrypt;
use Zend\Session\Container as SessionContainer;

class UserTable
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
     * @param Application\Model\User
     * @return Application\Model\User
     */
    public function verifyUser(User $currentUser)
    {
        $success = false;
        $bcrypt = new Bcrypt();

        //check if email exists and if so, return the user
        $rowset = $this->tableGateway->select(array('email' => $currentUser->email));
        $user = $rowset->current();

        if (!$user) {
            $success = false;
            throw new \Exception("Email does not exist");
        }

        if ($bcrypt->verify($currentUser->password, $user->password)) {
            $success = true;
        } else {
            $success = false;
            throw new \Exception("Invalid password");
        }

        return $user;
    }

    /**
     * @param Application\Model\User 
     * @return Application\Model\User
     */
    public function createUser(User $user)
    {
        $bcrypt = new Bcrypt();
        $user->password = $bcrypt->create($user->password);

        $user->user_id = uniqid(); //generate unique id

        $data = array(
            'user_id'   => $user->user_id,
            'firstname' => $user->firstname,
            'lastname'  => $user->lastname,
            'email'     => $user->email,
            'password'  => $user->password,
        );

        //check if email already exists
        $rowset = $this->tableGateway->select(array('email' => $user->email));
        $row = $rowset->current();
        
        if ($row) {
            $user = $row;
            throw new \Exception("Email already exists");
        } else {
            $this->tableGateway->insert($data);
            $rowset = $this->tableGateway->select(array('email' => $user->email));
            $user = $rowset->current();
        }
        return $user;
    }

    /**
     * @param int(8) 
     */
    public function deleteUser($user_id)
    {
        $this->tableGateway->delete(array('user_id' => $user_id));
    }

    /**
     * @param int(8) 
     * @return Application\Model\User
     */
    public function getUser($user_id)
    {
        $resultSet = $this->tableGateway->select(array('user_id' => $user_id));
        $user = $resultSet->current();
        return $user;
    }

    /**
     * @param string 
     * @return Application\Model\User
     */
    public function getUserByEmail($email)
    {
        $resultSet = $this->tableGateway->select(array('email' => $email));
        $user = $resultSet->current();
        return $user;
    }

    /**
     * @param Application\Model\User
     * @return Application\Model\User
     */
    public function updateUser(User $user)
    {
        $data = array();
        
        if (isset($user->password)) {
            $bcrypt = new Bcrypt();
            $user->password = $bcrypt->create($user->password);
            $data['password'] = $user->password;
        }
        
        if (isset($user->email)) {
            $data['email'] = $user->email;
            
            //check if email already exists
            $rowset = $this->tableGateway->select(array('email' => $user->email));
            $row = $rowset->current();
             if ($row) {
                 throw new \Exception("Email already exists");
             }
        }
        
        $this->tableGateway->update($data, array('user_id' => $user->user_id));
        
        $rowset = $this->tableGateway->select(array('user_id' => $user->user_id));
        $user = $rowset->current();
        
        return $user;
    }
}