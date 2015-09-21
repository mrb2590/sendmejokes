<?php
/**
 * SendMeJokes (http://www.sendmejokes.com/)
 *
 * @author    Mike Buonomo <mike@sendmjokes.com>
 * @link      https://github.com/mrb2590/sendmejokes
 */

namespace Application\Model;

class Category
{
    /**
     * @var int(8)
     */
    public $cat_id;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $url_name;

    /**
     * @param array
     */
    public function exchangeArray($data)
    {
        $this->cat_id = (!empty($data['cat_id'])) ? $data['cat_id'] : null;
        $this->name = (!empty($data['name'])) ? $data['name'] : null;
        $this->url_name = (!empty($data['url_name'])) ? $data['url_name'] : null;
    }
}