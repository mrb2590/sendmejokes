<?php
namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Crypt\Password\Bcrypt;
use Zend\Session\Container as SessionContainer;

class UserTable
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

    public function createUser(User $user)
    {
        $bcrypt = new Bcrypt();
        $user->password = $bcrypt->create($user->password);

        $data = array(
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

    public function deleteUser($id)
    {
        $this->tableGateway->delete(array('user_id' => $id));
    }

    public function getUser($id)
    {
        $id = (int) $id;
        $resultSet = $this->tableGateway->select(array('user_id' => $id));
        $user = $resultSet->current();
        return $user;
    }

    public function getUserByEmail($email)
    {
        $resultSet = $this->tableGateway->select(array('email' => $email));
        $user = $resultSet->current();
        return $user;
    }

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