<?php
namespace Application\Controller;

use Application\Model\User;
use Application\Model\UserCategory;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container as SessionContainer;

class UserController extends AbstractActionController
{
    protected $userTable;
    protected $categoryTable;
    protected $userCategoriesTable;

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

    public function createUserAction()
    {
        //set blank layout
        $this->layout('layout/blank');

        //start user session
        $this->session = new SessionContainer('user');
        $this->session->auth = false;   //authorized user

        $email = $this->getRequest()->getPost('email');
        $password = $this->getRequest()->getPost('password');
        $password2 = $this->getRequest()->getPost('password2');
        $categories = $this->getRequest()->getPost('category');
        $submit = $this->getRequest()->getPost('submit');

        $valid = false;
        $newUser = false;

        //validate
        if ($submit != 'submit') {
            $valid = false;
            $message = "Invalid request";
        } elseif (strpos($email, '@') === false || strpos($email, '.') === false) {
            $valid = false;
            $message = "Invalid email address";
        } elseif (strlen(utf8_decode($password)) < 6) {
            $valid = false;
            $message = "Password too short, must be at least six characters";
        } elseif ($password != $password2) {
            $valid = false;
            $message = "Passwords do not match";
        } else {
            $valid = true;
        }

        if ($valid) {
            try {
                //add user to db
                $user = new User();
                $user->email = $email;
                $user->password = $password;
                $user->firstname = '';
                $user->lastname = '';
                $user->user_id = 0;
                $newUser = $this->getUserTable()->createUser($user);

                //add user-categories to db
                foreach ($categories as $name => $cat_id) {
                    $userCategory = new UserCategory();
                    $userCategory->user_id = (int) $newUser->user_id;
                    $userCategory->cat_id = (int) $cat_id;
                    $this->getUserCategoriesTable()->addUserCategory($userCategory);
                }

                $message = 'Success';

                $this->session->auth = true;
                $this->session->user = new User();
                $this->session->user->email = $user->email;
                $this->session->user->user_id = $user->user_id;
            } catch (\Exception $e) {
                $message = 'Fail - ' . $e->getMessage();
            }
        }

        return new ViewModel(array(
            'message' => $message
        ));

    }
    
    public function signOutAction()
    {
        //set blank layout
        $this->layout('layout/blank');

        $this->session = new SessionContainer('user');
        unset($this->session->user);

        return new ViewModel(array(
            'message' => "Success"
        ));
    }
    
    public function signInAction()
    {
        //set blank layout
        $this->layout('layout/blank');

        $valid = false;

        //start user session
        $this->session = new SessionContainer('user');
        $this->session->auth = false;   //authorized user

        $user = new User();
        $user->email = $this->getRequest()->getPost('email');
        $user->password = $this->getRequest()->getPost('password');
        $submit = $this->getRequest()->getPost('submit');

        //validate
        if ($submit != 'submit') {
            $valid = false;
            $message = "Invalid request";
        } elseif (strpos($user->email, '@') === false || strpos($user->email, '.') === false) {
            $valid = false;
            $message = "Invalid email address";
        } else {
            $valid = true;
        }

        if($valid) {
            try {
                $user = $this->getUserTable()->verifyUser($user); //returns user object with user_id from db

                if (!$user) {
                    $message = "Invalid Credentials";
                } else {
                    $message = "Success";
                    $this->session->auth = true;
                    if (isset($this->session->user)) {
                        unset($this->session->user);
                    }
                    $this->session->user = new User();
                    $this->session->user->email = $user->email;
                    $this->session->user->user_id = $user->user_id;
                }
            } catch (\Exception $e) {
                //$message = 'Fail - ' . $e->getMessage();
                $message = "Invalid Credentials";
                $this->session->auth = false;
                if (isset($this->session->user)) {
                    unset($this->session->user);
                }
            }
        }

        return new ViewModel(array(
            'message' => $message
        ));
    }
}
