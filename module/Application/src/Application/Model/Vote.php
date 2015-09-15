<?php
namespace Application\Model;

class Vote
{
    public $id;
    public $joke_id;
    public $user_id;
    public $vote;
    
    public function exchangeArray($data)
    {
        $this->joke_id = (!empty($data['joke_id'])) ? $data['joke_id'] : null;
        $this->user_id = (!empty($data['user_id'])) ? $data['user_id'] : null;
        $this->vote = (!empty($data['vote'])) ? $data['vote'] : null;
    }
}