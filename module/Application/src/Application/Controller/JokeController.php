<?php
namespace Application\Controller;

use Application\Model\Vote;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container as SessionContainer;

class JokeController extends AbstractActionController
{
    protected $jokeTable;
    protected $viewJokeCategoriesTable;
    protected $voteTable;

    public function getJokeTable()
    {
        if (!$this->jokeTable) {
            $sm = $this->getServiceLocator();
            $this->jokeTable = $sm->get('Application\Model\JokeTable');
        }
        return $this->jokeTable;
    }

    public function getViewJokeCategoriesTable()
    {
        if (!$this->viewJokeCategoriesTable) {
            $sm = $this->getServiceLocator();
            $this->viewJokeCategoriesTable = $sm->get('Application\Model\ViewJokeCategoriesTable');
        }
        return $this->viewJokeCategoriesTable;
    }

    public function getVoteTable()
    {
        if (!$this->voteTable) {
            $sm = $this->getServiceLocator();
            $this->voteTable = $sm->get('Application\Model\VoteTable');
        }
        return $this->voteTable;
    }
    
    public function viewAllAction()
    {
        $this->session = new SessionContainer('user');
        $jokes = $this->getJokeTable()->fetchAll();
        $jokeCategories = $this->getViewJokeCategoriesTable()->fetchAll();
        $votes = $this->getVoteTable()->fetchAll();
        return new ViewModel(array(
            'jokes'          => $jokes,
            'jokeCategories' => $jokeCategories,
            'votes'          => $votes,
            'session'        => $this->session
        ));
    }

    public function voteAction()
    {
        //set blank layout
        $this->layout('layout/blank');
        $this->session = new SessionContainer('user');

        $vote = new Vote();
        $vote->joke_id = (int) $this->getRequest()->getPost('joke_id');
        $vote->user_id = (int) (isset($this->session->user)) ? $this->session->user->user_id : null;
        $vote->vote = $this->getRequest()->getPost('vote');
        $submit = $this->getRequest()->getPost('submit');

        //validate
        if ($submit != 'submit') {
            $valid = false;
            $message = "Invalid request";
            //return $this->redirect()->toRoute('Application', array('controller'=>$controllerName,'action' => $actionName));
        } elseif ($vote->joke_id == null || $vote->joke_id == '') {
            $valid = false;
            $message = "Missing joke ID";
        } elseif ($vote->user_id == null || $vote->user_id == '') {
            $valid = false;
            $message = "No user";
        } elseif ($vote->vote == null || $vote->vote == '') {
            $valid = false;
            $message = "Missing vote";
        } else {
            $valid = true;
        }

        if ($valid) {
            //update session
            foreach($this->session->userVotes as $key => $userVote) {
                if ($userVote->joke_id == $vote->joke_id && $userVote->user_id == $vote->user_id) {
                    $this->session->userVotes[$key] = $vote;
                }
            }
            $this->getVoteTable()->vote($vote);
            $message = "Success";
        }

        return new ViewModel(array(
            'message' => $message
        ));
    }
}
