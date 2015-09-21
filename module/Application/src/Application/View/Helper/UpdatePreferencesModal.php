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

class UpdatePreferencesModal extends AbstractHelper implements ServiceLocatorAwareInterface
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
        return $this->getView()->render('application/view-helper/update-preferences-modal', array(
            'categories' => $categories,
            'session'    => $this->session
        ));
    }
}