<?php
namespace Application\Controller;

use Application\Model\User;
use Application\Model\UserCategory;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container as SessionContainer;

class RouteController extends AbstractActionController
{
    protected $userTable;
    protected $categoryTable;
    protected $userCategoriesTable;
    protected $viewUserCategoriesTable;

    public function getUserTable()
    {
        if (!$this->userTable) {
            $sm = $this->getServiceLocator();
            $this->userTable = $sm->get('Application\Model\UserTable');
        }
        return $this->userTable;
    }

    public function getCategoryTable()
    {
        if (!$this->categoryTable) {
            $sm = $this->getServiceLocator();
            $this->categoryTable = $sm->get('Application\Model\CategoryTable');
        }
        return $this->categoryTable;
    }

    public function getUserCategoriesTable()
    {
        if (!$this->userCategoriesTable) {
            $sm = $this->getServiceLocator();
            $this->userCategoriesTable = $sm->get('Application\Model\UserCategoriesTable');
        }
        return $this->userCategoriesTable;
    }

    public function getViewUserCategoriesTable()
    {
        if (!$this->viewUserCategoriesTable) {
            $sm = $this->getServiceLocator();
            $this->viewUserCategoriesTable = $sm->get('Application\Model\ViewUserCategoriesTable');
        }
        return $this->viewUserCategoriesTable;
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
    
    //create user goes to thank you page
    public function thankyouAction()
    {
        $this->session = new SessionContainer('user');
        $this->session->auth = false;
        $fail = false;
        $userPostCategories = false;
        $newUser = false;

        if ($this->getRequest()->getPost('submit') != 'submit') {
            $fail = true;
            $message = "Huh?";
        } 

        if (!$fail) {
            $message = "Thank you for signing up!";

            $user = new User();
            $user->id = 0;
            $user->email = $this->getRequest()->getPost('email');
            $user->password = $this->getRequest()->getPost('password');
            $user->firstname = '';
            $user->lastname = '';
            $userPostCategories = $this->getRequest()->getPost('category');

            $newUser = $this->getUserTable()->createUser($user);

            if ($this->session->emailAlreadyExists) {
                $userPostCategories = false;
                $message = 'This email already Exists. If you forgot your password click <a id="reset-pass-link" href="/user/reset-password">here</a>';
            } else {
                foreach ($userPostCategories as $name => $cat_id) {
                    $uCat = new UserCategory();
                    $uCat->user_id = (int) $newUser->user_id;
                    $uCat->cat_id = (int) $cat_id;
                    $this->getUserCategoriesTable()->addUserCategory($uCat);
                }
            }
        }

        return new ViewModel(array(
            'user'       => $newUser,
            'message'    => $message,
            'categories' => $userPostCategories
        ));
    }
    
    public function signoutAction()
    {
        $this->session = new SessionContainer('user');
        unset($this->session->user);
        
        return $this->redirect()->toUrl('/');
    }
}
