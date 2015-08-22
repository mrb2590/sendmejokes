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
        return new ViewModel();
    }
}
