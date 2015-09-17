<?php
namespace Application\Controller;

use Application\Model\User;
use Application\Model\UserCategory;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container as SessionContainer;

class RouteController extends AbstractActionController
{
    protected $categoryTable;

    public function getCategoryTable()
    {
        if (!$this->categoryTable) {
            $sm = $this->getServiceLocator();
            $this->categoryTable = $sm->get('Application\Model\CategoryTable');
        }
        return $this->categoryTable;
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
}
