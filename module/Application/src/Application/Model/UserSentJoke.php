<?php
/**
 * SendMeJokes (http://www.sendmejokes.com/)
 *
 * @author    Mike Buonomo <mike@sendmjokes.com>
 * @link      https://github.com/mrb2590/sendmejokes
 */

namespace Application\Model;

class UserSentJoke
{
    /**
     * @var integer
     */
    public $id;

    /**
     * @var int(8)
     */
    public $joke_id;

    /**
     * @var string
     */
    public $user_id;

    /**
     * @var string
     */
    public $sent_on;

    /**
     * @param array
     */
    public function exchangeArray($data)
    {
        $this->id = (!empty($data['id'])) ? $data['id'] : null;
        $this->joke_id = (!empty($data['joke_id'])) ? $data['joke_id'] : null;
        $this->user_id = (!empty($data['user_id'])) ? $data['user_id'] : null;
        $this->sent_on = (!empty($data['sent_on'])) ? $data['sent_on'] : null;
    }
}