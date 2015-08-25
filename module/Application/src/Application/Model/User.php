<?php
namespace Application\Model;

class User
{
    public $user_id;
    public $firstname;
    public $lastname;
    public $email;
    public $password;

    public function exchangeArray($data)
    {
        $this->user_id   = (!empty($data['user_id'])) ? $data['user_id'] : null;
        $this->firstname = (!empty($data['firstname'])) ? $data['firstname'] : null;
        $this->lastname  = (!empty($data['lastname'])) ? $data['lastname'] : null;
        $this->email     = (!empty($data['email'])) ? $data['email'] : null;
        $this->password  = (!empty($data['password'])) ? $data['password'] : null;
    }
}