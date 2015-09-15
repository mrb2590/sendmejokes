<?php
namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;

class VoteTable
{
    protected $tableGateway;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function fetchAll()
    {
        $resultSet = $this->tableGateway->select();
        return $resultSet;
    }

    public function getVotesByUser($user_id)
    {
        $user_id  = (int) $user_id;
        $rowset = $this->tableGateway->select(array('user_id' => $user_id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("No votes found for this user");
        }
        return $row;
    }

    public function getVotesByJoke($joke_id)
    {
        $joke_id  = (int) $joke_id;
        $rowset = $this->tableGateway->select(array('joke_id' => $joke_id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("No votes found for this joke");
        }
        return $row;
    }

    public function addVote(Vote $vote)
    {
        $data = array(
            'joke_id' => $vote->joke_id,
            'user_id' => $user->user_id,
            'vote'    => $user->vote,
        );
        $this->tableGateway->insert($data);
    }

    public function removeVote($id)
    {
        $this->tableGateway->delete(array('id' => (int) $id));
    }

    public function updateVote(Vote $vote)
    {
        $data = array(
            'vote'    => $user->vote,
        );
        $this->tableGateway->update($data, array('joke_id' => $vote->joke_id, 'user_id' => $vote->user_id));
    }

    public function vote(Vote $vote)
    {
        $rowset = $this->tableGateway->select($data, array('joke_id' => $vote->joke_id, 'user_id' => $vote->user_id));
        $row = $rowset->current();

        if ($row) {
            $this->updateVote($vote); //if vote exists, update it
        } else {
            $this->addVote($vote); //otherwise add it
        }
    }
}