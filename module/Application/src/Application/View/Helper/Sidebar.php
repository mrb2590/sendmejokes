<?php
namespace Application\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\Session\Container as SessionContainer;

class Sidebar extends AbstractHelper implements ServiceLocatorAwareInterface
{
    use \Zend\ServiceManager\ServiceLocatorAwareTrait;

    protected $categoryTable;

    public function getCategoryTable()
    {
        if (!$this->categoryTable) {
            $sm = $this->getServiceLocator()->getServiceLocator();
            $this->categoryTable = $sm->get('Application\Model\CategoryTable');
        }
        return $this->categoryTable;
    }

    public function __invoke()
    {
        $this->session = new SessionContainer('user');
        
        $categories = $this->getCategoryTable()->fetchAll();
        return $this->getView()->render('application/viewhelper/sidebar', array(
            'categories'   => $categories,
            'session' => $this->session
        ));
    }
}