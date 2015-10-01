<?php
/**
 * SendMeJokes (http://www.sendmejokes.com/)
 *
 * @author    Mike Buonomo <mike@sendmjokes.com>
 * @link      https://github.com/mrb2590/sendmejokes
 */

namespace Application\Model;

class User
{
    /**
     * @var string(13)
     */
    public $user_id;

    /**
     * @var string
     */
    public $username;

    /**
     * @var string
     */
    public $email;

    /**
     * @var string
     */
    public $password;

    /**
     * @param array
     */
    public function exchangeArray($data)
    {
        $this->user_id   = (!empty($data['user_id'])) ? $data['user_id'] : null;
        $this->username = (!empty($data['username'])) ? $data['username'] : null;
        $this->email     = (!empty($data['email'])) ? $data['email'] : null;
        $this->password  = (!empty($data['password'])) ? $data['password'] : null;
    }
}