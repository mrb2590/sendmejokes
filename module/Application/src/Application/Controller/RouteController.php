<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class RouteController extends AbstractActionController
{
    public function comingSoonAction()
    {
        return new ViewModel();
    }

    public function homeAction()
    {
        $sm = $this->getServiceLocator();
        $categoryTable = $sm->get('Application\Model\CategoryTable');
        $categories = $categoryTable->fetchAll();
        return new ViewModel(
            array(
                'categories' => $categories
            )
        );
    }
}
