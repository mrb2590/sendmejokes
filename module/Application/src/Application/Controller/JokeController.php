<?php
/**
 * SendMeJokes (http://www.sendmejokes.com/)
 *
 * @author    Mike Buonomo <mike@sendmjokes.com>
 * @link      https://github.com/mrb2590/sendmejokes
 */

namespace Application\Controller;

use Application\Model\Joke;
use Application\Model\JokeCategory;
use Application\Model\Vote;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container as SessionContainer;

class JokeController extends ApplicationController
{
    /**
     * @return Zend\View\Model\ViewModel
     */
    public function viewAction()
    {
        $this->session = new SessionContainer('user');
        $category = $this->params()->fromRoute('category');
        $catflag = false;
        $message = false;
        $categoryObject = false;
        $l = $this->params()->fromQuery('limit');
        $p = $this->params()->fromQuery('page');
        $limit = (isset($l)) ? (int) $l : 20;
        $page = (isset($p)) ? (int) $p : 1;

        //filter by category if passed in route
        if (isset($category)) {
            $catflag = true;
            try {
                $categoryObject = $this->getCategoryTable()->getCategoryByUrlName($category);
                $message = "Success";
            } catch (\Excepection $e) {
                $message = $e;
            }
            $jokes = $this->getViewJokeCategoriesTable()->fetchPaginatedByCategory($categoryObject->cat_id, $limit, $page);
            $allJokes = $this->getViewJokeCategoriesTable()->getJokesByCategory($categoryObject->cat_id);
        } else {
            //get all jokes paginated
            $jokes = $this->getJokeTable()->fetchPaginated($limit, $page);
            $allJokes = $this->getJokeTable()->fetchAll();
        }

        $total = count($allJokes);
        $maxPages = ((int) ceil($total / $limit));

        $jokeCategories = $this->getViewJokeCategoriesTable()->fetchAll();
        $votes = $this->getVoteTable()->fetchAll();

        return new ViewModel(array(
            'jokes'          => $jokes,
            'jokeCategories' => $jokeCategories,
            'votes'          => $votes,
            'session'        => $this->session,
            'message'        => $message,
            'catflag'        => $catflag,
            'categoryObject' => $categoryObject,
            'page'           => $page,
            'maxPages'       => $maxPages,
            'total'          => $total,
        ));
    }

    /**
     * @return Zend\View\Model\ViewModel
     */
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

    /**
     * @return Zend\View\Model\ViewModel
     */
    public function manageAction()
    {
        $this->session = new SessionContainer('user');

        $categories = $this->getCategoryTable()->fetchAll();

        //only I can view this page muahahaha
        if (!isset($this->session->user->user_id) || $this->session->user->user_id != '10000000') {
            return $this->notFoundAction();
        }

        return new ViewModel(array(
            'categories' => $categories
        ));
    }

    /**
     * @return Zend\View\Model\ViewModel
     */
    public function addAction()
    {
        //set blank layout
        $this->layout('layout/blank');
        $this->session = new SessionContainer('user');
        $joke = $this->getRequest()->getPost('joke');
        $answer = $this->getRequest()->getPost('answer');
        $submit = $this->getRequest()->getPost('submit');
        $categories = $this->getRequest()->getPost('category');
        $valid = false;

        //only I can view this page muahahaha
        if (!isset($this->session->user->user_id) || $this->session->user->user_id != '10000000') {
            $message = "Fail";
        } elseif ($submit != 'submit') {
            $message = "Invalid request";
        } elseif (!$this->validateInput($joke, '')) {
            $message = "Joke is required";
        } elseif (!$this->validateInput($categories, '')) {
            $message = "At least one category is required";
        } else {
            $valid = true;
        }

        if ($valid) {
            $jokeObject = new Joke();
            $jokeObject->joke = $joke;
            $jokeObject->answer = $answer;
            try {
                $newJoke = $this->getJokeTable()->addJoke($jokeObject); //add joke to db
                foreach ($categories as $name => $cat_id) {
                    $jokeCategory = new JokeCategory();
                    $jokeCategory->joke_id = (int) $newJoke->joke_id;
                    $jokeCategory->cat_id = (int) $cat_id;
                    $this->getJokeCategoriesTable()->addJokeCategory($jokeCategory);
                }
                $message = "Success";
            } catch (\Excepection $e) {
                $message = $e;
            }
        }

        return new ViewModel(array(
            'message' => $message
        ));
    }

    /**
     * @return Zend\View\Model\ViewModel
     */
    public function removeAction()
    {
        //set blank layout
        $this->layout('layout/blank');
        $this->session = new SessionContainer('user');
        $joke_id = $this->getRequest()->getPost('joke_id');
        $submit = $this->getRequest()->getPost('submit');
        $categories = $this->getRequest()->getPost('category');
        $valid = false;

        //only I can view this page muahahaha
        if (!isset($this->session->user->user_id) || $this->session->user->user_id != '10000000') {
            $message = "Fail";
        } elseif ($submit != 'submit') {
            $message = "Invalid request";
        } elseif (!$this->validateInput($joke_id, '')) {
            $message = "Joke ID is required";
        } else {
            $valid = true;
            $message = "Success";
        }

        if ($valid) {
            $this->getJokeTable()->deleteJoke($joke_id);
        }

        return new ViewModel(array(
            'message' => $message
        ));
    }
}
