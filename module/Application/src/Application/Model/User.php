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
     * @var int(8)
     */
    public $user_id;

    /**
     * @var string
     */
    public $firstname;

    /**
     * @var string
     */
    public $lastname;

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
        $this->firstname = (!empty($data['firstname'])) ? $data['firstname'] : null;
        $this->lastname  = (!empty($data['lastname'])) ? $data['lastname'] : null;
        $this->email     = (!empty($data['email'])) ? $data['email'] : null;
        $this->password  = (!empty($data['password'])) ? $data['password'] : null;
    }
}