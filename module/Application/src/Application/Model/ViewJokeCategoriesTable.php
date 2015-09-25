<?php
/**
 * SendMeJokes (http://www.sendmejokes.com/)
 *
 * @author    Mike Buonomo <mike@sendmjokes.com>
 * @link      https://github.com/mrb2590/sendmejokes
 */

namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;

class ViewJokeCategoriesTable
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
        $resultSet->buffer();
        return $resultSet;
    }

    /**
     * @param int(8) $cat_id
     * @param integer $limit
     * @param integer $page
     * @return Zend\Db\ResultSet\ResultSet 
     */
    public function fetchPaginatedByCategory($cat_id, $limit, $page)
    {
        $cat_id = (int) $cat_id;
        $limit = (int) $limit;
        $page = (int) $page;
        $offset = $limit * ($page - 1);
        $resultSet = $this->tableGateway->select(function (Select $select) use ($cat_id, $limit, $offset) {
            $select->where('cat_id = ' . $cat_id)->limit($limit)->offset($offset);
        });
        $resultSet->buffer();
        return $resultSet;
    }

    /**
     * @param int(8) 
     * @return Zend\Db\ResultSet\ResultSet 
     */
    public function getJokesByCategory($cat_id)
    {
        $resultSet = $this->tableGateway->select(array('cat_id' => $cat_id));
        return $resultSet; 
    }
}