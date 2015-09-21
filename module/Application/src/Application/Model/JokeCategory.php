<?php
/**
 * SendMeJokes (http://www.sendmejokes.com/)
 *
 * @author    Mike Buonomo <mike@sendmjokes.com>
 * @link      https://github.com/mrb2590/sendmejokes
 */

namespace Application\Model;

class JokeCategory
{
    /**
     * @var int(8)
     */
    public $id;

    /**
     * @var int(8)
     */
    public $joke_id;

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
        $this->joke_id = (!empty($data['joke_id'])) ? $data['joke_id'] : null;
        $this->cat_id  = (!empty($data['cat_id'])) ? $data['cat_id'] : null;
    }
}