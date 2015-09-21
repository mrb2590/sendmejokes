<?php
/**
 * SendMeJokes (http://www.sendmejokes.com/)
 *
 * @author    Mike Buonomo <mike@sendmjokes.com>
 * @link      https://github.com/mrb2590/sendmejokes
 */

namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;

class CategoryTable
{
    /**
     * @var Zend\Db\TableGateway\TableGateway
     */
    protected $tableGateway;

    /**
     * @param Zend\Db\TableGateway\TableGateway $tableGateway
     */
    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    /**
     * @return Zend\Db\ResultSet\ResultSet 
     */
    public function fetchAll()
    {
        $resultSet = $this->tableGateway->select();
        return $resultSet;
    }

    /**
     * @param int(8)
     * @return Application\Model\Category
     */
    public function getCategory($cat_id)
    {
        $cat_id  = (int) $cat_id;
        $rowset = $this->tableGateway->select(array('cat_id' => $cat_id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Category not found");
        }
        return $row;
    }

    /**
     * @param string
     * @return Application\Model\Category
     */
    public function getCategoryByUrlName($url_name)
    {
        $rowset = $this->tableGateway->select(array('url_name' => $url_name));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Category not found");
        }
        return $row;
    }

    /**
     * @param int(*)
     */
    public function deleteCategory($cat_id)
    {
        $this->tableGateway->delete(array('cat_id' => (int) $cat_id));
    }
}