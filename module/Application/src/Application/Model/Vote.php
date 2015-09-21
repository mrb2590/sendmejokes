<?php
/**
 * SendMeJokes (http://www.sendmejokes.com/)
 *
 * @author    Mike Buonomo <mike@sendmjokes.com>
 * @link      https://github.com/mrb2590/sendmejokes
 */

namespace Application\Model;

class Vote
{
    /**
     * @var int(8)
     */
    public $joke_id;

    /**
     * @var int(8)
     */
    public $user_id;

    /**
     * @var signed integer
     */
    public $vote;

    /**
     * @param array
     */
    public function exchangeArray($data)
    {
        $this->joke_id = (!empty($data['joke_id'])) ? $data['joke_id'] : null;
        $this->user_id = (!empty($data['user_id'])) ? $data['user_id'] : null;
        $this->vote = (!empty($data['vote'])) ? $data['vote'] : null;
    }
}