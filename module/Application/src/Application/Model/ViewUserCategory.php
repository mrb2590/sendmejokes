<?php
/**
 * SendMeJokes (http://www.sendmejokes.com/)
 *
 * @author    Mike Buonomo <mike@sendmjokes.com>
 * @link      https://github.com/mrb2590/sendmejokes
 */

namespace Application\Model;

class ViewUserCategory
{
    /**
     * @var string
     */
    public $email;

    /**
     * @var string
     */
    public $name;

    /**
     * @param array
     */
    public function exchangeArray($data)
    {
        $this->email = (!empty($data['email'])) ? $data['email'] : null;
        $this->name  = (!empty($data['name'])) ? $data['name'] : null;
    }
}