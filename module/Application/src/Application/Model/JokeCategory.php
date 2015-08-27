<?php
namespace Application\Model;

class JokeCategory
{
    public $id;
    public $joke_id;
    public $cat_id;

    public function exchangeArray($data)
    {
        $this->id      = (!empty($data['id'])) ? $data['id'] : null;
        $this->joke_id = (!empty($data['joke_id'])) ? $data['joke_id'] : null;
        $this->cat_id  = (!empty($data['cat_id'])) ? $data['cat_id'] : null;
    }
}