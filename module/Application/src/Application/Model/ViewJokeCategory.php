<?php
namespace Application\Model;

class ViewJokeCategory
{
    public $joke_id;
    public $joke;
    public $answer;
    public $cat_id;
    public $name;

    public function exchangeArray($data)
    {
        $this->joke_id = (!empty($data['joke_id'])) ? $data['joke_id'] : null;
        $this->joke = (!empty($data['joke'])) ? $data['joke'] : null;
        $this->answer = (!empty($data['answer'])) ? $data['answer'] : null;
        $this->cat_id = (!empty($data['cat_id'])) ? $data['cat_id'] : null;
        $this->name = (!empty($data['name'])) ? $data['name'] : null;
    }
}