<?php
namespace Application\Model;

class Joke
{
    public $joke_id;
    public $joke;
    public $answer;

    public function exchangeArray($data)
    {
        $this->joke_id = (!empty($data['joke_id'])) ? $data['joke_id'] : null;
        $this->joke    = (!empty($data['joke'])) ? $data['joke'] : null;
        $this->answer  = (!empty($data['answer'])) ? $data['answer'] : null;
    }
}