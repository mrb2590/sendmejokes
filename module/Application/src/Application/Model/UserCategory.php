<?php
/**
 * SendMeJokes (http://www.sendmejokes.com/)
 *
 * @author    Mike Buonomo <mike@sendmjokes.com>
 * @link      https://github.com/mrb2590/sendmejokes
 */

namespace Application\Model;

class UserCategory
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
     * @var int(8)
     */
    public $cat_id;

    /**
     * @param array
     */
    public function exchangeArray($data)
    {
        $this->id      = (!empty($data['id'])) ? $data['id'] : null;
        $this->user_id = (!empty($data['user_id'])) ? $data['user_id'] : null;
        $this->cat_id  = (!empty($data['cat_id'])) ? $data['cat_id'] : null;
    }
}