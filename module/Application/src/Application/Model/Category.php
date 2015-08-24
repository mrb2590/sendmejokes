<?php
namespace Application\Model;

class Category
{
    public $cat_id;
    public $name;
    
    public function exchangeArray($data)
    {
        $this->cat_id = (!empty($data['cat_id'])) ? $data['cat_id'] : null;
        $this->name   = (!empty($data['name'])) ? $data['name'] : null;
    }
}