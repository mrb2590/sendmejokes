<?php
namespace Application\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\Session\Container as SessionContainer;

class Sidebar extends AbstractHelper implements ServiceLocatorAwareInterface
{
    use \Zend\ServiceManager\ServiceLocatorAwareTrait;

    public function __invoke()
    {
        $this->session = new SessionContainer('user');
        
        $sm = $this->getServiceLocator()->getServiceLocator();
        $categoryTable = $sm->get('Application\Model\CategoryTable');
        $categories = $categoryTable->fetchAll();
        return $this->getView()->render('application/viewhelper/sidebar', array(
            'categories' => $categories,
            'session'    => $this->session
        ));
    }
}