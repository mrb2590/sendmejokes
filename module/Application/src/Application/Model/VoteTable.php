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
        $resultSet->buffer();
        return $resultSet;
    }

    public function getVotesByUser($user_id)
    {
        $user_id  = (int) $user_id;
        $resultSet = $this->tableGateway->select(array('user_id' => $user_id));
        $resultSet->buffer();
        return $resultSet;
    }

    public function getVotesByJoke($joke_id)
    {
        $joke_id  = (int) $joke_id;
        $resultSet = $this->tableGateway->select(array('joke_id' => $joke_id));
        return $resultSet;
    }

    public function addVote(Vote $vote)
    {
        $data = array(
            'joke_id' => $vote->joke_id,
            'user_id' => $vote->user_id,
            'vote'    => $vote->vote,
        );
        $this->tableGateway->insert($data);
    }

    public function removeVotesByUser($user_id)
    {
        $this->tableGateway->delete(array('user_id' => (int) $user_id));
    }

    public function removeVotesByJoke($joke_id)
    {
        $this->tableGateway->delete(array('joke_id' => (int) $joke_id));
    }

    public function updateVote(Vote $vote)
    {
        $data = array(
            'vote'    => $vote->vote,
        );
        $this->tableGateway->update($data, array('joke_id' => $vote->joke_id, 'user_id' => $vote->user_id));
    }

    public function vote(Vote $vote)
    {
        //check if vote exists (are they changing upvote to downvote?)
        $rowset = $this->tableGateway->select(array('joke_id' => $vote->joke_id, 'user_id' => $vote->user_id));
        $row = $rowset->current();

        if ($row) {
            $this->updateVote($vote); //if vote exists, update it
        } else {
            $this->addVote($vote); //otherwise add it
        }
    }
}