<?php
namespace Application\Model;

class UserCategory
{
    public $id;
    public $user_id;
    public $cat_id;

    public function exchangeArray($data)
    {
        $this->id      = (!empty($data['id'])) ? $data['id'] : null;
        $this->user_id = (!empty($data['user_id'])) ? $data['user_id'] : null;
        $this->cat_id  = (!empty($data['cat_id'])) ? $data['cat_id'] : null;
    }
}