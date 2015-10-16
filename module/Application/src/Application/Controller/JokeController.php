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
        $userVotes = (isset($this->session->user)) ? $this->getVoteTable()->getVotesByUser($this->session->user->user_id): false;
        $category = $this->params()->fromRoute('category');
        $get_joke_id = $this->params()->fromRoute('joke_id');
        $search = $this->params()->fromQuery('search');
        $total = false;
        $jokes = false;
        $searchflag = false;
        $jokeflag = false;
        $catflag = false;
        $message = false;
        $categoryObject = false;
        $l = $this->params()->fromQuery('limit');
        $p = $this->params()->fromQuery('page');
        $limit = (isset($l)) ? (int) $l : 20;
        $page = (isset($p)) ? (int) $p : 1;

        //filter by joke id or category if passed in route
        if (isset($get_joke_id)) {
            $jokeflag = true;
            $joke = $this->getJokeTable()->getJoke($get_joke_id);
            $jokes = array();
            $jokes[0] = $joke;
        } elseif (isset($category)) {
            $catflag = true;
            try {
                $categoryObject = $this->getCategoryTable()->getCategoryByUrlName($category);
                $message = "Success";
            } catch (\Exception $e) {
                $message = "Category not found";
            }
            $jokes = $this->getViewJokeCategoriesTable()->fetchPaginatedByCategory($categoryObject->cat_id, $limit, $page);
            $total = $this->getViewJokeCategoriesTable()->getJokeCountByCategory($categoryObject->cat_id);
        } elseif (isset($search)) {
            $searchflag = true;
            try {
                $jokes = $this->getJokeTable()->getPaginatedSearchResults($search, $limit, $page);
                $total = $this->getJokeTable()->getAllSearchResultsCount($search);
                $message = "Success";
            } catch(\Exception $e) {
                $message = "No results found";
            }
        } else {
            //get all jokes paginated
            $jokes = $this->getJokeTable()->fetchPaginated($limit, $page);
            $total = $this->getJokeTable()->getJokeCount();
        }

        $totalOnPage = count($jokes);
        $total = ($jokeflag) ? 1 : $total;
        $maxPages = ((int) ceil($total / $limit));

        $jokeCategories = $this->getViewJokeCategoriesTable()->fetchAll();
        $votes = $this->getVoteTable()->fetchAll();

        return new ViewModel(array(
            'jokes'          => $jokes,
            'jokeCategories' => $jokeCategories,
            'votes'          => $votes,
            'session'        => $this->session,
            'message'        => $message,
            'jokeflag'       => $jokeflag,
            'catflag'        => $catflag,
            'searchflag'     => $searchflag,
            'search'         => $search,
            'categoryObject' => $categoryObject,
            'page'           => $page,
            'maxPages'       => $maxPages,
            'total'          => $total,
            'totalOnPage'    => $totalOnPage,
            'userVotes'      => $userVotes,
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
        } elseif (!$this->validateInput($vote->joke_id, '')) {
            $valid = false;
            $message = "Missing joke ID";
        } elseif (!$this->validateInput($vote->user_id, '')) {
            $valid = false;
            $message = "No user";
        } elseif (!$this->validateInput($vote->vote, '')) {
            $valid = false;
            $message = "Missing vote";
        } else {
            $valid = true;
        }

        if ($valid) {
            //if vote exists in session already, update it
            $voteExists = false;
            $i = 0;
            foreach($this->session->userVotes as $key => $userVote) {
                if ($userVote->joke_id == $vote->joke_id && $userVote->user_id == $vote->user_id) {
                    $this->session->userVotes[$key] = $vote;
                    $voteExists = true;
                }
                $i++;
            }
            //if it doesnt exist in session, add it
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
    public function emailVoteAction()
    {
        $key = $this->params()->fromQuery('k');
        $email = $this->params()->fromQuery('email');
        $joke_id = (int) $this->params()->fromQuery('joke_id');
        $userVote = (int) $this->params()->fromQuery('vote');

        //validate
        if (!$this->validateInput($key, '')) {
            $valid = false;
            $message = "Invalid key";
        } elseif (!$this->validateInput($email, 'email')) {
            $valid = false;
            $message = "Invalid email";
        } elseif (!$this->validateInput($joke_id, '')) {
            $valid = false;
            $message = "Invalid joke ID";
        } elseif (!$this->validateInput($userVote, '')) {
            $valid = false;
            $message = "Invalid vote";
        } else {
            //get user id from table and check agaisnt $key
            $user = $this->getUserTable()->getUserByEmail($email);
            if (hash('sha256', $user->user_id) != $key) {
                $valid = false;
                $message = "Invalid request";
            } else {
                $valid = true;
            }
        }

        //if valid save the vote
        if ($valid) {
            $vote = new Vote();
            $vote->joke_id = $joke_id;
            $vote->user_id = $user->user_id;
            $vote->vote = $userVote;
            $this->getVoteTable()->vote($vote);
            $message = "Your vote has been saved.";
        }


        //$message = var_export($user, true);

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

        //only I can view this page muahahaha
        if (!isset($this->session->user->email) || $this->session->user->email != 'mrb2590@gmail.com') {
            return $this->notFoundAction();
        }

        $categories = $this->getCategoryTable()->fetchAll();
        $jokeCategories = false;
        $message = false;
        $joke = false;
        $joke_id = $this->params()->fromQuery('joke_id');

        if($this->validateInput($joke_id, '')) {
            try {
                $jokeCategories = $this->getJokeCategoriesTable()->getJokeCategoriesByJokeId($joke_id);
                $joke = $this->getJokeTable()->getJoke($joke_id);
            } catch (\Exception $e) {
                $joke = false;
                $jokeCategories = false;
                $message = 'Unable to find joke';;
            }
        }

        return new ViewModel(array(
            'categories'     => $categories,
            'jokeCategories' => $jokeCategories,
            'joke'           => $joke,
            'message'        => $message,
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
        if (!isset($this->session->user->email) || $this->session->user->email != 'mrb2590@gmail.com') {
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
            } catch (\Exception $e) {
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

    /**
     * @return Zend\View\Model\ViewModel
     */
    public function getShareButtonsAction()
    {
        //set blank layout
        $this->layout('layout/blank');
        $joke_id = $this->getRequest()->getPost('joke_id');
        $submit = $this->getRequest()->getPost('submit');
        $facebookButtonHTML = false;
        $valid = false;

        if ($submit != 'submit') {
            $message = "Invalid request";
        } elseif (!$this->validateInput($joke_id, '')) {
            $message = "Joke ID is required";
        } else {
            $valid = true;
            $message = "Success";
        }

        if ($valid) {
            $joke = $this->getJokeTable()->getJoke($joke_id);
            $joke->joke = trim(preg_replace('/[\r|\n|\r\n]+/', '%0A', $joke->joke));
            $joke->joke = preg_replace('/"/', '%22', $joke->joke);
            $joke->answer = trim(preg_replace('/[\r|\n|\r\n]+/', '%0A', $joke->answer));
            $joke->answer = preg_replace('/"/', '%22', $joke->answer);

            //build mailto string
            $emailSubject = '?subject=Check out this joke from SendMeJokes!';
            $emailBody = '&amp;body=' . $joke->joke . '%0A';
            $emailBody .= trim(preg_replace('/\s+/', ' ', $joke->answer)) . '%0A%0A';
            $emailBody .= 'See more at http:%2F%2Fwww.sendmejokes.com';
            $email = $emailSubject . $emailBody;

            //build HTML for share buttons
            $emailButtonHTML = '<a href="mailto:' . $email . '"><i class="fa fa-envelope-square"></i></a>';
            $facebookButtonHTML .= '<div class="fb-share-button" data-href="http://www.sendmejokes.com/jokes/view/' . $joke_id . '/" data-layout="icon"></div>';
        }

        return new ViewModel(array(
            'message'            => $message,
            'facebookButtonHTML' => $facebookButtonHTML,
            'emailButtonHTML'    => $emailButtonHTML,
        ));
    }

    /**
     * @return Zend\View\Model\ViewModel
     */
    public function updateJokeCategoriesAction()
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
        } elseif (!$this->validateInput($categories, '')) {
            $message = "At least one category is required";
        } else {
            $valid = true;
        }

        if ($valid) {
            try {
                $joke = $this->getJokeTable()->getJoke($joke_id); //add joke to db
                //remove all joke categories for this joke
                $this->getJokeCategoriesTable()->deleteJokeCategoriesByJoke($joke_id);
                //now add the new ones
                foreach ($categories as $name => $cat_id) {
                    $jokeCategory = new JokeCategory();
                    $jokeCategory->joke_id = (int) $joke_id;
                    $jokeCategory->cat_id = (int) $cat_id;
                    $this->getJokeCategoriesTable()->addJokeCategory($jokeCategory);
                }
                $message = "Success";
            } catch (\Exception $e) {
                $message = $e;
            }
        }

        return new ViewModel(array(
            'message' => $message
        ));
    }
}
