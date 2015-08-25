<?php
namespace Application\Model;

class ViewUserCategory
{
    public $email;
    public $name;

    public function exchangeArray($data)
    {
        $this->email = (!empty($data['email'])) ? $data['email'] : null;
        $this->name  = (!empty($data['name'])) ? $data['name'] : null;
    }
}