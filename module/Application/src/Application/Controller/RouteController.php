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
        $categoryTable = $sm->get('Application\Model\UserTable');
        $users = $categoryTable->fetchAll();
        return new ViewModel(
            array(
                'users' => $users
            )
        );
    }
}
