<?php
/**
 * SendMeJokes (http://www.sendmejokes.com/)
 *
 * @author    Mike Buonomo <mike@sendmjokes.com>
 * @link      https://github.com/mrb2590/sendmejokes
 */

namespace Application\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\Session\Container as SessionContainer;

class Sidebar extends AbstractHelper implements ServiceLocatorAwareInterface
{
    use \Zend\ServiceManager\ServiceLocatorAwareTrait;

    /**
     * @var Application\Model\CategoryTable
     */
    protected $categoryTable;

    /**
     * @return Application\Model\CategoryTable
     */
    public function getCategoryTable()
    {
        if (!$this->categoryTable) {
            $sm = $this->getServiceLocator()->getServiceLocator();
            $this->categoryTable = $sm->get('Application\Model\CategoryTable');
        }
        return $this->categoryTable;
    }

    /**
     * @return Zend\View\Model\ViewModel
     */
    public function __invoke()
    {
        $this->session = new SessionContainer('user');
        $categories = $this->getCategoryTable()->fetchAll();
        $userSession = (isset($this->session->user->user_id)) ? true : false;
        return $this->getView()->render('application/view-helper/sidebar', array(
            'categories'  => $categories,
            'session'     => $this->session,
            'userSession' => $userSession,
        ));
    }
}