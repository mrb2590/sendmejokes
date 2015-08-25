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

    public function getUser($id)
    {
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('id' => $id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
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

        $id = (int) $user->id;

        //check if email already exists
        $rowset = $this->tableGateway->select(array('email' => $user->email));
        $row = $rowset->current();

        $this->session = new SessionContainer('user');
        
        if ($row) {
            $user = $row;
            $this->session->auth = false;
            $this->session->emailAlreadyExists = true;
        } else {
            $this->tableGateway->insert($data);
            $rowset = $this->tableGateway->select(array('email' => $user->email));
            $user = $rowset->current();

            $this->session->auth = true;
            $this->session->user = new User();
            $this->session->user->email = $user->email;
            $this->session->user->id = $user->user_id;
            $this->session->emailAlreadyExists = false;
        }
        return $user;
    }

    public function deleteUser($id)
    {
        $this->tableGateway->delete(array('id' => (int) $id));
    }
}