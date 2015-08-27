<?php
namespace Application\Model;

class ViewJokeCategory
{
    public $joke_id;
    public $name;

    public function exchangeArray($data)
    {
        $this->joke_id = (!empty($data['joke_id'])) ? $data['joke_id'] : null;
        $this->name    = (!empty($data['name'])) ? $data['name'] : null;
    }
}