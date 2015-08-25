<?php
namespace Application\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

class Sidebar extends AbstractHelper implements ServiceLocatorAwareInterface
{
    use \Zend\ServiceManager\ServiceLocatorAwareTrait;

    public function __invoke()
    {
        $sm = $this->getServiceLocator()->getServiceLocator();
        $categoryTable = $sm->get('Application\Model\CategoryTable');
        $categories = $categoryTable->fetchAll();
        return $this->getView()->render('application/viewhelper/sidebar', array('categories' => $categories));
    }
}