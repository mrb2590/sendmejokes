<?php
/**
 * SendMeJokes (http://www.sendmejokes.com/)
 *
 * @author    Mike Buonomo <mike@sendmjokes.com>
 * @link      https://github.com/mrb2590/sendmejokes
 */

namespace Application\Controller;

use Application\Model\Joke;
use Application\Model\User;
use Application\Model\Category;
use Application\Model\Vote;
use Application\Model\UserCategory;
use Application\Model\UserExcludeCategory;
use Application\Model\JokeCategory;
use Application\Model\ViewUserCategory;
use Application\Model\ViewJokeCategory;
use Application\Model\UserSentJoke;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container as SessionContainer;

class ApplicationController extends AbstractActionController
{
    /**
     * @var Application\Model\JokeTable
     */
    protected $jokeTable;

    /**
     * @var Application\Model\UserTable
     */
    protected $userTable;

    /**
     * @var Application\Model\CategoryTable
     */
    protected $categoryTable;

    /**
     * @var Application\Model\VoteTable
     */
    protected $voteTable;

    /**
     * @var Application\Model\UserCategoriesTable
     */
    protected $userCategoriesTable;

    /**
     * @var Application\Model\UserExcludeCategoriesTable
     */
    protected $userExcludeCategoriesTable;

    /**
     * @var Application\Model\JokeCategoriesTable
     */
    protected $jokeCategoriesTable;

    /**
     * @var Application\Model\ViewUserCategoriesTable
     */
    protected $viewUserCategoriesTable;

    /**
     * @var Application\Model\ViewJokeCategoriesTable
     */
    protected $viewJokeCategoriesTable;

    /**
     * @var Application\Model\UserSentJokesTable
     */
    protected $userSentJokesTable;

    /**
     * @return Application\Model\JokeTable
     */
    public function getJokeTable()
    {
        if (!$this->jokeTable) {
            $sm = $this->getServiceLocator();
            $this->jokeTable = $sm->get('Application\Model\JokeTable');
        }
        return $this->jokeTable;
    }

    /**
     * @return Application\Model\UserTable
     */
    public function getUserTable()
    {
        if (!$this->userTable) {
            $sm = $this->getServiceLocator();
            $this->userTable = $sm->get('Application\Model\UserTable');
        }
        return $this->userTable;
    }

    /**
     * @return Application\Model\CategoryTable
     */
    public function getCategoryTable()
    {
        if (!$this->categoryTable) {
            $sm = $this->getServiceLocator();
            $this->categoryTable = $sm->get('Application\Model\CategoryTable');
        }
        return $this->categoryTable;
    }

    /**
     * @return Application\Model\VoteTable
     */
    public function getVoteTable()
    {
        if (!$this->voteTable) {
            $sm = $this->getServiceLocator();
            $this->voteTable = $sm->get('Application\Model\VoteTable');
        }
        return $this->voteTable;
    }

    /**
     * @return Application\Model\UserCategoriesTable
     */
    public function getUserCategoriesTable()
    {
        if (!$this->userCategoriesTable) {
            $sm = $this->getServiceLocator();
            $this->userCategoriesTable = $sm->get('Application\Model\UserCategoriesTable');
        }
        return $this->userCategoriesTable;
    }

    /**
     * @return Application\Model\UserExcludeCategoriesTable
     */
    public function getUserExcludeCategoriesTable()
    {
        if (!$this->userExcludeCategoriesTable) {
            $sm = $this->getServiceLocator();
            $this->userExcludeCategoriesTable = $sm->get('Application\Model\UserExcludeCategoriesTable');
        }
        return $this->userExcludeCategoriesTable;
    }

    /**
     * @return Application\Model\JokeCategoriesTable
     */
    public function getJokeCategoriesTable()
    {
        if (!$this->jokeCategoriesTable) {
            $sm = $this->getServiceLocator();
            $this->jokeCategoriesTable = $sm->get('Application\Model\JokeCategoriesTable');
        }
        return $this->jokeCategoriesTable;
    }

    /**
     * @return Application\Model\ViewUserCategoriesTable
     */
    public function getViewUserCategoriesTable()
    {
        if (!$this->viewUserCategoriesTable) {
            $sm = $this->getServiceLocator();
            $this->viewUserCategoriesTable = $sm->get('Application\Model\ViewUserCategoriesTable');
        }
        return $this->viewUserCategoriesTable;
    }

    /**
     * @return Application\Model\ViewJokeCategoriesTable
     */
    public function getViewJokeCategoriesTable()
    {
        if (!$this->viewJokeCategoriesTable) {
            $sm = $this->getServiceLocator();
            $this->viewJokeCategoriesTable = $sm->get('Application\Model\ViewJokeCategoriesTable');
        }
        return $this->viewJokeCategoriesTable;
    }

    /**
     * @return Application\Model\UserSentJokesTable
     */
    public function getUserSentJokesTable()
    {
        if (!$this->userSentJokesTable) {
            $sm = $this->getServiceLocator();
            $this->userSentJokesTable = $sm->get('Application\Model\UserSentJokesTable');
        }
        return $this->userSentJokesTable;
    }

    /**
     * @return Zend\View\Model\ViewModel
     */
    public function comingSoonAction()
    {
        return new ViewModel();
    }

    /**
     * @param string $input
     * @param string $type
     * @return boolean
     */
    public function validateInput($input, $type)
    {
        $valid = (isset($input) && $input != '') ? true : false;

        if (isset($type)) {
            switch ($type) {
                case 'email':
                    $valid = (filter_var($input, FILTER_VALIDATE_EMAIL));
                    break;
                case 'password':
                    $valid = (strlen(utf8_decode($input)) >= 6);
                    break;
                case 'username':
                    $input = utf8_decode($input);
                    $valid = (strlen($input) >= 6 && strlen($input) <= 16);
                    break;
            }
        }

        return $valid;
    }
}
