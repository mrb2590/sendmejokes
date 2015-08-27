<?php
namespace Application\Model;

class ViewJokeCategory
{
    public $joke;
    public $name;

    public function exchangeArray($data)
    {
        $this->joke = (!empty($data['joke'])) ? $data['joke'] : null;
        $this->name = (!empty($data['name'])) ? $data['name'] : null;
    }
}