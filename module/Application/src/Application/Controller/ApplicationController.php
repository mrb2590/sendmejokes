<?php
namespace Application\Controller;

use Application\Model\Joke;
use Application\Model\User;
use Application\Model\Category;
use Application\Model\Vote;
use Application\Model\UserCategory;
use Application\Model\JokeCategory;
use Application\Model\ViewUserCategory;
use Application\Model\ViewJokeCategory;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container as SessionContainer;

class ApplicationController extends AbstractActionController
{
    protected $jokeTable;
    protected $userTable;
    protected $categoryTable;
    protected $voteTable;
    protected $userCategoriesTable;
    protected $jokeCategoriesTable;
    protected $viewUserCategoriesTable;
    protected $viewJokeCategoriesTable;

    public function getJokeTable()
    {
        if (!$this->jokeTable) {
            $sm = $this->getServiceLocator();
            $this->jokeTable = $sm->get('Application\Model\JokeTable');
        }
        return $this->jokeTable;
    }

    public function getUserTable()
    {
        if (!$this->userTable) {
            $sm = $this->getServiceLocator();
            $this->userTable = $sm->get('Application\Model\UserTable');
        }
        return $this->userTable;
    }

    public function getCategoryTable()
    {
        if (!$this->categoryTable) {
            $sm = $this->getServiceLocator();
            $this->categoryTable = $sm->get('Application\Model\CategoryTable');
        }
        return $this->categoryTable;
    }

    public function getVoteTable()
    {
        if (!$this->voteTable) {
            $sm = $this->getServiceLocator();
            $this->voteTable = $sm->get('Application\Model\VoteTable');
        }
        return $this->voteTable;
    }

    public function getUserCategoriesTable()
    {
        if (!$this->userCategoriesTable) {
            $sm = $this->getServiceLocator();
            $this->userCategoriesTable = $sm->get('Application\Model\UserCategoriesTable');
        }
        return $this->userCategoriesTable;
    }

    public function getJokeCategoriesTable()
    {
        if (!$this->jokeCategoriesTable) {
            $sm = $this->getServiceLocator();
            $this->jokeCategoriesTable = $sm->get('Application\Model\JokeCategoriesTable');
        }
        return $this->jokeCategoriesTable;
    }

    public function getViewUserCategoriesTable()
    {
        if (!$this->viewUserCategoriesTable) {
            $sm = $this->getServiceLocator();
            $this->viewUserCategoriesTable = $sm->get('Application\Model\ViewUserCategoriesTable');
        }
        return $this->viewUserCategoriesTable;
    }

    public function getViewJokeCategoriesTable()
    {
        if (!$this->viewJokeCategoriesTable) {
            $sm = $this->getServiceLocator();
            $this->viewJokeCategoriesTable = $sm->get('Application\Model\ViewJokeCategoriesTable');
        }
        return $this->viewJokeCategoriesTable;
    }

    public function comingSoonAction()
    {
        return new ViewModel();
    }
}
