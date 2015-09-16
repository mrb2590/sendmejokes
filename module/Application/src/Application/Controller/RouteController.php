<?php
namespace Application\Controller;

use Application\Model\User;
use Application\Model\UserCategory;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container as SessionContainer;

class RouteController extends AbstractActionController
{
    protected $jokeTable;
    protected $categoryTable;
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

    public function getCategoryTable()
    {
        if (!$this->categoryTable) {
            $sm = $this->getServiceLocator();
            $this->categoryTable = $sm->get('Application\Model\CategoryTable');
        }
        return $this->categoryTable;
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

    
    public function comingSoonAction()
    {
        return new ViewModel();
    }

    public function homeAction()
    {
        $categories = $this->getCategoryTable()->fetchAll();
        return new ViewModel(array(
            'categories' => $categories
        ));
    }
    
    public function jokesAction()
    {
        $jokes = $this->getJokeTable()->fetchAll();
        $jokeCategories = $this->getViewJokeCategoriesTable()->fetchAll();
        $votes = $this->getVoteTable()->fetchAll();
        return new ViewModel(array(
            'jokes'          => $jokes,
            'jokeCategories' => $jokeCategories,
            'votes'          => $votes
        ));
    }
}
