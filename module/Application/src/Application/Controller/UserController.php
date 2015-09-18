<?php
namespace Application\Controller;

use Application\Model\User;
use Application\Model\UserCategory;
use Application\Model\ViewUserCategory;
use Application\Model\Vote;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container as SessionContainer;

class UserController extends ApplicationController
{
    public function signUpAction()
    {
        $categories = $this->getCategoryTable()->fetchAll();
        return new ViewModel(array(
            'categories' => $categories
        ));
    }
    
    public function saveUserSession(User $user) {
        $this->session->auth = true;

        //save user id and email
        $this->session->user = new User();
        $this->session->user->email = $user->email;
        $this->session->user->user_id = $user->user_id;

        //save user votes
        $votes = $this->getVoteTable()->getVotesByUser($user->user_id);
        $i = 0;
        $this->session->userVotes = array();
        foreach ($votes as $vote) {
            $this->session->userVotes[$i] = new Vote();
            $this->session->userVotes[$i] = $vote;
            $i++;
        }

        //save UserCategories
        $userCategories = $this->getUserCategoriesTable()->getUserCategories($user);
        $i = 0;
        $this->session->userCategories = array();
        foreach ($userCategories as $userCategory) {
            $this->session->userCategories[$i] = new UserCategory();
            $this->session->userCategories[$i] = $userCategory;
            $i++;
        }

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
        unset($this->session->userVotes);
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
                $message = $e->getMessage();
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
            $tempUser->password = $passwordOld;
            try {
                $tempUser = $this->getUserTable()->verifyUser($tempUser);
                $valid = true;
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
    
    public function resetPasswordFormAction()
    {
        $encryptedID = $this->getRequest()->getQuery('id');

        if ($encryptedID == null || $encryptedID == '') {
            $message = "Fail";
        } else {
            $message = "get-form";
        }

        return new ViewModel(array(
            'message' => $message,
            'id'      => $encryptedID
        ));
    }
    
    public function resetPasswordAction()
    {
        //set blank layout
        $this->layout('layout/blank');
        $valid = false;
        $submit = $this->getRequest()->getPost('submit');
        $email = $this->getRequest()->getPost('email');
        $password1 = $this->getRequest()->getPost('password1');
        $password2 = $this->getRequest()->getPost('password2');
        $hashedID = $this->getRequest()->getPost('id');

        //validate
        if ($submit != 'submit') {
            $valid = false;
            $message = "Invalid request";
        } elseif (strpos($email, '@') === false || strpos($email, '.') === false) {
            $valid = false;
            $message = "Invalid email address";
        } elseif ($password1 == null || $password1 == '') {
            $valid = false;
            $message = "Must enter a new password";
        } elseif ($password1 != $password2) {
            $valid = false;
            $message = "Password must match";
        } else {
            $valid = true;
        }

        if ($valid) {
            $user = new User();
            $user = $this->getUserTable()->getUserByEmail($email);

            if($user) {
                $userHashedID = hash('sha256', $user->user_id);
                if ($userHashedID == $hashedID) {
                    $user->password = $this->getRequest()->getPost('password1');
                    $user->email = null; //as to not update the email address, which will cause error saying it already exists
                    $this->getUserTable()->updateUser($user);
                    $message = "Success";
                } else {
                    $message = "Make sure you are using the correct email address and the link from your reset password email";
                }
            } else {
                $message = "Make sure you are using the correct email address and the link from your reset password email";
            }
        }

        return new ViewModel(array(
            'message' => $message
        ));
    }
    
    public function sendResetPasswordEmailAction()
    {
        //set blank layout
        $this->layout('layout/blank');
        $valid = false;
        $submit = $this->getRequest()->getPost('submit');
        $email = $this->getRequest()->getPost('email');

        //validate
        if ($submit != 'submit') {
            $valid = false;
            $message = "Invalid request";
        } elseif (strpos($email, '@') === false || strpos($email, '.') === false) {
            $valid = false;
            $message = "Invalid email address";
        } else {
            $valid = true;
        }

        if ($valid) {
            $user = $this->getUserTable()->getUserByEmail($email);

            if ($user) {
                $message = "Success";

                $subject = 'Reset Password - SendMeJokes';

                $headers  = 'MIME-Version: 1.0' . "\r\n";
                $headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
                $headers .= 'From: SendMeJokes <reset-password@sendmejokes.com>' . "\r\n";
                $headers .= 'Reply-To: SendMeJokes <reset-password@sendmejokes.com>' . "\r\n";

                $body =  '<html>';
                $body .= '<body style="background-color: #fff; padding: 20px; font-family: Courier;">';
                $body .=    '<div style="width: 100%;">';
                $body .=        '<div style="background-color: #cc4646;">';
                $body .=            '<div style="color: #fff; text-align: center; font-size: 16px; height: 40px; line-height: 40px;">Reset Password</div>';
                $body .=        '</div>';
                $body .=        '<div style="padding: 20px; background-color: #eee; font-size: 14px;">';
                $body .=            '<p>If you have not requested to reset your password, then ignore this email.</p>';
                $body .=            '<p>Otherwise click the link below to reset your password.</p>';
                $body .=            '<p><a href="http://' . $_SERVER['HTTP_HOST'] . '/user/reset-password-form?id=' . hash('sha256', $user->user_id) . '">http://' . $_SERVER['HTTP_HOST'] . '/user/reset-password-form?id=' . hash('sha256', $user->user_id) . '</a></p>';
                $body .=         '</div>';
                $body .=     '</div>';
                $body .= '</body>';
                $body .= '</html>';

                mail($user->email, $subject, $body, $headers);
            } else {
                $message = "Success"; //will not let user know email does not exist
            }
        }

        return new ViewModel(array(
            'message' => $message
        ));
    }
}
