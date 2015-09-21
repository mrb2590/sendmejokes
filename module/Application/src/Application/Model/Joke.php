<?php
/**
 * SendMeJokes (http://www.sendmejokes.com/)
 *
 * @author    Mike Buonomo <mike@sendmjokes.com>
 * @link      https://github.com/mrb2590/sendmejokes
 */

namespace Application\Model;

class Joke
{
    /**
     * @var int(8)
     */
    public $joke_id;

    /**
     * @var string
     */
    public $joke;

    /**
     * @var string
     */
    public $answer;

    /**
     * @param array
     */
    public function exchangeArray($data)
    {
        $this->joke_id = (!empty($data['joke_id'])) ? $data['joke_id'] : null;
        $this->joke    = (!empty($data['joke'])) ? $data['joke'] : null;
        $this->answer  = (!empty($data['answer'])) ? $data['answer'] : null;
    }
}