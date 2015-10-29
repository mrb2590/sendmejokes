<?php
/**
 * SendMeJokes (http://www.sendmejokes.com/)
 *
 * @author    Mike Buonomo <mike@sendmjokes.com>
 * @link      https://github.com/mrb2590/sendmejokes
 */

namespace Application\Model;

class UserDay
{
    /**
     * @var int(8)
     */
    public $id;

    /**
     * @var int(8)
     */
    public $user_id;

    /**
     * @var string
     */
    public $day;

    /**
     * @param array
     */
    public function exchangeArray($data)
    {
        $this->id = (!empty($data['id'])) ? $data['id'] : null;
        $this->user_id = (!empty($data['user_id'])) ? $data['user_id'] : null;
        $this->day = (!empty($data['day'])) ? $data['day'] : null;
    }
}