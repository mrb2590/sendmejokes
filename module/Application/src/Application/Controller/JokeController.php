<?php
namespace Application\Controller;

use Application\Model\Vote;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container as SessionContainer;

class JokeController extends ApplicationController
{
    public function viewAction()
    {
        $this->session = new SessionContainer('user');
        $category = $this->params()->fromRoute('category');
        $catflag = false;
        $message = false;
        $categoryName = false;

        //filter by category if passed in route
        if (isset($category)) {
            $catflag = true;
            try {
                $categoryName = $this->getCategoryTable()->getCategory($category);
                $message = "Success";
            } catch (\Excepection $e) {
                $message = $e;
            }
            $jokes = $this->getViewJokeCategoriesTable()->getJokesByCategory($category);
        } else {
        $jokes = $this->getJokeTable()->fetchAll();
        }

        $jokeCategories = $this->getViewJokeCategoriesTable()->fetchAll();
        $votes = $this->getVoteTable()->fetchAll();

        return new ViewModel(array(
            'jokes'          => $jokes,
            'jokeCategories' => $jokeCategories,
            'votes'          => $votes,
            'session'        => $this->session,
            'message'        => $message,
            'catflag'        => $catflag,
            'categoryName'   => $categoryName,
        ));
    }

    public function voteAction()
    {
        //fail route if category is passed in url
        $category = $this->params()->fromRoute('category');
        if(isset($category)) {
            return $this->notFoundAction();
        }

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

            //update vote exists in session already, update it
            $voteExists = false;
            $i = 0;
            foreach($this->session->userVotes as $key => $userVote) {
                if ($userVote->joke_id == $vote->joke_id && $userVote->user_id == $vote->user_id) {
                    $this->session->userVotes[$key] = $vote;
                    $voteExists = true;
                }
                $i++;
            }

            //if it doesnt exist is session, add it
            if (!$voteExists) {
                $this->session->userVotes[$i + 1] = $vote;
            }

            $this->getVoteTable()->vote($vote);
            $message = "Success";
        }

        return new ViewModel(array(
            'message' => $message
        ));
    }
}
