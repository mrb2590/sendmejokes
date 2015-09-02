<?php
namespace Application\Controller;

use Application\Model\User;
use Application\Model\UserCategory;
use Application\Model\ViewUserCategory;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container as SessionContainer;

class UserController extends AbstractActionController
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

    public function saveUserSession(User $user) {
        $this->session->auth = true;

        //save user id and email
        $this->session->user = new User();
        $this->session->user->email = $user->email;
        $this->session->user->user_id = $user->user_id;

        //save UserCategories
        $i = 0;
        $this->session->userCategories = array();
        $userCategories = $this->getUserCategoriesTable()->getUserCategories($user);
        foreach ($userCategories as $userCategory) {
            $this->session->userCategories[$i] = new UserCategory();
            $this->session->userCategories[$i] = $userCategory;
            $i++;
        }

        unset($i);

        //save ViewUserCategories
        $viewUserCategories = $this->getViewUserCategoriesTable()->getUserCategories($user);
        $i = 0;
        $this->session->viewUserCategories = array();
        foreach ($viewUserCategories as $viewUserCategory) {
            $this->session->viewUserCategories[$i] = new ViewUserCategory();
            $this->session->viewUserCategories[$i] = $viewUserCategory;
            $i++;
        }
    }

    public function destroyUserSession() {
        $this->session = new SessionContainer('user');
        $this->session->auth = false;
        unset($this->session->user);
        unset($this->session->userCategories);
        unset($this->session->viewUserCategories);
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
                if (isset($categories)) {
                    foreach ($categories as $name => $cat_id) {
                        $userCategory = new UserCategory();
                        $userCategory->user_id = (int) $newUser->user_id;
                        $userCategory->cat_id = (int) $cat_id;
                        $this->getUserCategoriesTable()->addUserCategory($userCategory);
                    }
                }

                $message = 'Success';

                //set session
                $this->saveUserSession($newUser);

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

        $this->destroyUserSession();

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
                    
                    //set session
                    $this->saveUserSession($user);
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
    
    public function updateUserAction()
    {
        //set blank layout
        $this->layout('layout/blank');
        $this->session = new SessionContainer('user');
        
        $email = $this->getRequest()->getPost('email');
        $passwordOld = $this->getRequest()->getPost('password-old');
        $password = $this->getRequest()->getPost('password');
        $password2 = $this->getRequest()->getPost('password2');
        $deleteAccount = $this->getRequest()->getPost('delete-account');
        $submit = $this->getRequest()->getPost('submit');
        $categories = $this->getRequest()->getPost('category');

        //validate
        if ($submit != 'submit') {
            $valid = false;
            $message = "Invalid request";
        } elseif ($email != null && $email != '' && (strpos($email, '@') === false || strpos($email, '.') === false)) {
            $valid = false;
            $message = "Invalid email address";
        } elseif ($password !== $password2) {
            $valid = false;
            $message = "Passwords do not match";
        } elseif ($passwordOld == null || $passwordOld == '') {
            $valid = false;
            if ($password != null && $password != '') {
                $valid = false;
                $message = "Must enter current password";
            } else {
                $valid = true;
            }
        } elseif ($passwordOld != null && $passwordOld != '') {
            $tempUser = new User();
            $tempUser = $this->getUserTable()->getUser($this->session->user->user_id);
            try {
                $tempUser = $this->getUserTable()->verifyUser($tempUser);
                if($tempUser) {
                    $valid = true;
                }
            } catch (\Exception $e) {
                $valid = false;
                $message = $e->getMessage();
            }
        } else {
            $valid = true;
        }

        if ($valid) {
            if (isset($deleteAccount) && $deleteAccount == 1) {
                //delete all categories first
                $this->getUserCategoriesTable()->deleteCategoryByUserId($this->session->user->user_id);                
                $this->getUserTable()->deleteUser($this->session->user->user_id);
                $this->destroyUserSession();
                $message = "Account deleted";
            } else {
                $updateUser = false;
                
                //update email or password is input was not blank
                if ($email != '' || $password != '') {
                    $user = new User();
                    $user->user_id = $this->session->user->user_id;
                    
                    if ($email != '') {
                        $user->email = $email;
                    } else {
                        $user->email = null;
                    }
                    
                    if ($password != '') {
                        $user->password = $password;
                    } else {
                        $user->password = null;
                    }
                    
                    $updateUser = true;
                }
                
                try {
                    $updatedUser = new User();
                    
                    if ($updateUser) {
                        $updatedUser = $this->getUserTable()->updateUser($user);
                    } else {
                        $updatedUser = $this->getUserTable()->getUser($this->session->user->user_id);
                    }
                    
                    //first delete all user categories, then re-add them as per update form
                    $this->getUserCategoriesTable()->deleteCategoryByUserId($updatedUser->user_id);
                    
                    //add user-categories to db
                    if ($categories != null) {
                        foreach ($categories as $name => $cat_id) {
                            $userCategory = new UserCategory();
                            $userCategory->user_id = (int) $updatedUser->user_id;
                            $userCategory->cat_id = (int) $cat_id;
                            $this->getUserCategoriesTable()->addUserCategory($userCategory);
                        }
                    }
                    
                    $this->saveUserSession($updatedUser); //update session with new categories/email/password etc..
                    $message = "Success";
                } catch (\Exception $e) {
                    $message = $e->getMessage();
                }
            }
        }

        return new ViewModel(array(
            'message' => $message
        ));
    }
}
