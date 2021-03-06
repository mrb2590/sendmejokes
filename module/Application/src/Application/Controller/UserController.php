<?php
/**
 * SendMeJokes (http://www.sendmejokes.com/)
 *
 * @author    Mike Buonomo <mike@sendmjokes.com>
 * @link      https://github.com/mrb2590/sendmejokes
 */

namespace Application\Controller;

use Application\Model\User;
use Application\Model\UserCategory;
use Application\Model\UserDay;
use Application\Model\UserExcludeCategory;
use Application\Model\ViewUserCategory;
use Application\Model\Vote;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container as SessionContainer;

class UserController extends ApplicationController
{
    /**
     * @return Zend\View\Model\ViewModel
     */
    public function viewAction()
    {
        $this->session = new SessionContainer('user');
        $username = $this->params()->fromRoute('username');

        //only current signed in user can view this page
        if(isset($this->session->user->username)) {
            if ($this->session->user->username != $username) {
                return $this->notFoundAction();
            }
        } else {
             $this->redirect()->toRoute('home');
        }

        $categories = $this->getCategoryTable()->fetchAll();

        return new ViewModel(array(
            'session'    => $this->session,
            'categories' => $categories,
            'days'       => $this->days,
        ));
    }

    /**
     * @return Zend\View\Model\ViewModel
     */
    public function signUpAction()
    {
        $this->session = new SessionContainer('user');
        $categories = $this->getCategoryTable()->fetchAll();
        return new ViewModel(array(
            'categories' => $categories,
            'days'       => $this->days,
            'session'    => $this->session,
        ));
    }
    
    /**
     * @param Application\Model\User
     */
    public function saveUserSession(User $user) {
        $this->session = new SessionContainer('user');
        $this->session->auth = true;

        //save user id and email
        $this->session->user = new User();
        $this->session->user->email = $user->email;
        $this->session->user->user_id = $user->user_id;
        $this->session->user->username = $user->username;

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

        //save UserDays
        $userDays = $this->getUserDaysTable()->getUserDays($user);
        $i = 0;
        $this->session->userDays = array();
        foreach ($userDays as $userDay) {
            $this->session->userDays[$i] = new UserDay();
            $this->session->userDays[$i] = $userDay;
            $i++;
        }

        //save UserExcludeCategories
        $userExcludeCategories = $this->getUserExcludeCategoriesTable()->getUserExcludeCategories($user);
        $i = 0;
        $this->session->userExcludeCategories = array();
        foreach ($userExcludeCategories as $userExcludeCategory) {
            $this->session->userExcludeCategories[$i] = new UserCategory();
            $this->session->userExcludeCategories[$i] = $userExcludeCategory;
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

    /**
     * Unsets a User session
     */
    public function destroyUserSession() {
        $this->session = new SessionContainer('user');
        $this->session->getManager()->getStorage()->clear('user');
    }

    /**
     * @return Zend\View\Model\ViewModel
     */
    public function createUserAction()
    {
        //set blank layout
        $this->layout('layout/blank');

        //start user session
        $this->session = new SessionContainer('user');
        $this->session->auth = false;   //authorized user

        $email = $this->getRequest()->getPost('email');
        $username = $this->getRequest()->getPost('username');
        $password = $this->getRequest()->getPost('password');
        $password2 = $this->getRequest()->getPost('password2');
        $categories = $this->getRequest()->getPost('category');
        $days = $this->getRequest()->getPost('days');
        $excludeCategories = $this->getRequest()->getPost('excludeCategory');
        $submit = $this->getRequest()->getPost('submit');

        $valid = false;
        $newUser = false;

        //validate
        if ($submit != 'submit') {
            $valid = false;
            $message = "Invalid request";
        } elseif (!$this->validateInput($email, 'email')) {
            $valid = false;
            $message = "Invalid email address";
        } elseif (!$this->validateInput($username, 'username')) {
            $valid = false;
            $message = "Username must be a minimum of six characters and no larger than 16";
        } elseif (!$this->validateInput($password, 'password')) {
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
                $user->username = $username;
                $user->user_id = 0;
                $newUser = $this->getUserTable()->createUser($user);

                //add user-categories to db
                if (isset($categories)) {
                    foreach ($categories as $name => $cat_id) {
                        $userCategory = new UserCategory();
                        $userCategory->user_id = $newUser->user_id;
                        $userCategory->cat_id = (int) $cat_id;
                        $this->getUserCategoriesTable()->addUserCategory($userCategory);
                    }
                }

                //add user exclude categories to db
                if (isset($excludeCategories)) {
                    foreach ($excludeCategories as $name => $cat_id) {
                        $userExcludeCategory = new UserExcludeCategory();
                        $userExcludeCategory->user_id = $newUser->user_id;
                        $userExcludeCategory->cat_id = (int) $cat_id;
                        $this->getUserExcludeCategoriesTable()->addUserExcludeCategory($userExcludeCategory);
                    }
                }

                //add user-days to db
                if (isset($days)) {
                    foreach ($days as $day) {
                        $userDay = new UserDay();
                        $userDay->user_id = $newUser->user_id;
                        $userDay->day = $day;
                        $this->getUserDaysTable()->addUserDay($userDay);
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

    /**
     * @return Zend\View\Model\ViewModel
     */
    public function signOutAction()
    {
        //set blank layout
        $this->layout('layout/blank');

        $this->destroyUserSession();

        if (strpos($_SERVER['REQUEST_URI'], 'user') !== false) {
            $message = "Redirect";
        } else {
            $message = "Success";
        }

        return new ViewModel(array(
            'message' => $message
        ));
    }

    /**
     * @return Zend\View\Model\ViewModel
     */
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
            $message = "Invalid request.";
        } elseif (!$this->validateInput($user->email, 'email')) {
            $valid = false;
            $message = "Invalid email address.";
        } else {
            $valid = true;
        }

        if($valid) {
            try {
                $user = $this->getUserTable()->verifyUser($user); //returns user object with user_id from db

                if (!$user) {
                    $message = "Invalid Credentials.";
                } else {
                    $message = $user->username;
                    
                    //set session
                    $this->saveUserSession($user);
                }
            } catch (\Exception $e) {
                //$message = 'Fail - ' . $e->getMessage();
                $message = "Invalid Credentials.";
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

    /**
     * @return Zend\View\Model\ViewModel
     */
    public function updateUserAction()
    {
        //set blank layout
        $this->layout('layout/blank');
        $this->session = new SessionContainer('user');

        $email = $this->getRequest()->getPost('email');
        $username = $this->getRequest()->getPost('username');
        $passwordOld = $this->getRequest()->getPost('password-old');
        $password = $this->getRequest()->getPost('password');
        $password2 = $this->getRequest()->getPost('password2');
        $deleteAccount = $this->getRequest()->getPost('delete-account');
        $submit = $this->getRequest()->getPost('submit');
        $categories = $this->getRequest()->getPost('category');
        $days = $this->getRequest()->getPost('days');
        $excludeCategories = $this->getRequest()->getPost('excludeCategory');

        //validate
        if(!isset($this->session->user)) {
            $message = "Must be signed in";
            $valid = false;
        } elseif ($submit != 'submit') {
            $valid = false;
            $message = "Invalid request";
        } elseif ((isset($email) && $email != '') && !$this->validateInput($email, 'email')) {
            $valid = false;
            $message = "Invalid email address";
        } elseif ((isset($username) && $username != '') && !$this->validateInput($username, 'username')) {
            $valid = false;
            $message = "Username must be a minimum of six characters and no larger than 16";
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
                $this->getUserDaysTable()->deleteUserDaysByUserId($this->session->user->user_id);
                $this->getUserCategoriesTable()->deleteCategoryByUserId($this->session->user->user_id);
                $this->getUserExcludeCategoriesTable()->deleteCategoryByUserId($this->session->user->user_id);
                $this->getUserTable()->deleteUser($this->session->user->user_id);
                $this->destroyUserSession();
                $message = "Account deleted";
            } else {
                $updateUser = false;
                $updateUsernameFlag = ($username != '') ? true : false;
                
                //update email, username, or password if input was not blank
                if ($email != '' || $password != '' || $username != '') {
                    $user = new User();
                    $user->user_id = $this->session->user->user_id;
                    $user->email = ($email != '') ? $email : null;
                    $user->username = ($username != '') ? $username : null;
                    $user->password = ($password != '') ? $password : null;
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

                    $categories = ($categories == null) ? array() : $categories;
                    //first delete all categoriess then replace
                    $this->getUserCategoriesTable()->deleteCategoryByUserId($updatedUser->user_id);
                    foreach ($categories as $name => $cat_id) {
                        $userCategory = new UserCategory();
                        $userCategory->user_id = $updatedUser->user_id;
                        $userCategory->cat_id = (int) $cat_id;
                        $this->getUserCategoriesTable()->addUserCategory($userCategory);
                    }

                    $excludeCategories = ($excludeCategories == null) ? array() : $excludeCategories;
                    //first delete all exclude categoriess then replace
                    $this->getUserExcludeCategoriesTable()->deleteCategoryByUserId($updatedUser->user_id);
                    foreach ($excludeCategories as $name => $cat_id) {
                        $userExcludeCategory = new UserExcludeCategory();
                        $userExcludeCategory->user_id = $updatedUser->user_id;
                        $userExcludeCategory->cat_id = (int) $cat_id;
                        $this->getUserExcludeCategoriesTable()->addUserExcludeCategory($userExcludeCategory);
                    }

                    $days = ($days == null) ? array() : $days;
                    
                    //first delete all user-days then replace
                    $this->getUserDaysTable()->deleteUserDaysByUserId($updatedUser->user_id);
                    foreach ($days as $day) {
                        $userDay = new UserDay();
                        $userDay->user_id = $updatedUser->user_id;
                        $userDay->day = $day;
                        $this->getUserDaysTable()->addUserDay($userDay);
                    }

                    $this->saveUserSession($updatedUser); //update session with new categories/email/password etc..

                    $message = ($updateUsernameFlag) ? "Username updated" : "Success";
                } catch (\Exception $e) {
                    $message = $e->getMessage();
                }
            }
        }

        return new ViewModel(array(
            'message' => $message,
        ));
    }

    /**
     * @return Zend\View\Model\ViewModel
     */
    public function resetPasswordFormAction()
    {
        $encryptedID = $this->params()->fromRoute('reset_pass_id');

        if (!$this->validateInput($encryptedID, '')) {
            $message = "Fail";
        } else {
            $message = "get-form";
        }

        return new ViewModel(array(
            'message' => $message,
            'id'      => $encryptedID
        ));
    }

    /**
     * @return Zend\View\Model\ViewModel
     */
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
        } elseif (!$this->validateInput($email, 'email')) {
            $valid = false;
            $message = "Invalid email address";
        } elseif (!$this->validateInput($password1, 'password')) {
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
                $possibleHashedIds = array();
                //generate a hash for each of the last 15 minutes
                //hash from query string must match one, otherwise it could be expired
                for ($i = 0; $i < 15; $i++) {
                    $possibleHashedIds[hash('sha256', $user->user_id . date('Ymdhi', strtotime('- ' . $i . ' minutes')))] = true;
                }

                if (isset($possibleHashedIds[$hashedID]) && $possibleHashedIds[$hashedID]) {
                    $user->password = $password1;
                    $user->email = null; //as to not update the email address, which will cause error saying it already exists
                    $user->username = null; //as to not update the email address, which will cause error saying it already exists
                    $this->getUserTable()->updateUser($user);
                    $message = "Success";
                } else {
                    $message = "Make sure this link is not older than 15 minutes, otherwise it has expired. Submit the reset password form again to send a new email.";
                }
            } else {
                $message = "Make sure you are using the correct email address and link from your reset password email";
            }
        }

        return new ViewModel(array(
            'message' => $message,
        ));
    }

    /**
     * @return Zend\View\Model\ViewModel
     */
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
                $hashedID = hash('sha256', $user->user_id . date('Ymdhi'));
                $message = "Success";

                $subject = 'Reset Password - SendMeJokes';

                $headers  = 'MIME-Version: 1.0' . "\r\n";
                $headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
                $headers .= 'From: SendMeJokes <reset-password@sendmejokes.com>' . "\r\n";
                $headers .= 'Reply-To: SendMeJokes <reset-password@sendmejokes.com>' . "\r\n";
                
                $body  = '<html>';
                $body .= '<body style="color:#333333;background-color:#E5E5E5;padding:20px;font-family:Arial,Helvetica,sans-serif;font-size:16px;">';
                $body .=     '<div class="panel" style="width:100%;max-width:600px;background-color:#FFFFFF;border:1px solid #D8D8D8;margin:0 auto;">';
                $body .=         '<div class="panel-heading" style="background-color:#333333;color:#FFFFFF;padding:10px;border-bottom:5px solid #2196F3;text-align:center;font-size:22px;">';
                $body .=             '<img src="http://www.sendmejokes.com/img/logo.png" height="50" style="height:50px;width:auto;"/>';
                $body .=             '<br/>';
                $body .=             'Reset Password ';
                $body .=         '</div><!-- /.panel-heading -->';
                $body .=         '<div class="panel-body" style="padding:20px;">';
                $body .=            '<p>If you have not requested to reset your password, then ignore this email. This does not mean you account is at risk; anyone can send this email.</p>';
                $body .=            '<p>However, if you do intend to reset your passowrd, click the link below.</p>';
                $body .=            '<p><a href="http://' . $_SERVER['HTTP_HOST'] . '/user/reset-password-form/' . $hashedID . '/">http://' . $_SERVER['HTTP_HOST'] . '/user/reset-password-form/' . $hashedID . '/</a></p>';
                $body .=            '<p>This link will expire 15 minutes after this email was sent.</p>';
                $body .=         '</div><!-- /.panel-body -->';
                $body .=         '<div class="panel-footer" style="border-top:1px solid #333;background-color:#F5F5F5;padding:20px;text-align:center;">';
                $body .=             '<a href="https://www.facebook.com/sharer/sharer.php?u=http%3A//www.sendmejokes.com/jokes/"><img src="http://www.sendmejokes.com/img/icon_link_out.png" height="20" style="height:20px;width:auto;"/></a>';
                $body .=             '<a href="https://plus.google.com/share?url=http%3A//www.sendmejokes.com/jokes/"><img src="http://www.sendmejokes.com/img/icon_link_out2.png" height="20" style="height:20px;width:auto;margin-left:5px;"/></a>';
                $body .=         '</div><!-- /.panel-footer -->';
                $body .=     '</div><!-- /.panel -->';
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

    /**
     * @return Zend\View\Model\ViewModel
     */
    public function unsubscribeAction()
    {
        $valid = false;
        $key = $this->params()->fromQuery('k');
        $email = $this->params()->fromQuery('email');
        //validate
        if (!$this->validateInput($key, '')) {
            $valid = false;
            $message = "Invalid request";
        } elseif (!$this->validateInput($email, 'email')) {
            $valid = false;
            $message = "Invalid request";
        } else {
            $valid = true;
        }
        if ($valid) {
            $user = new User();
            $user = $this->getUserTable()->getUserByEmail($email);
            if ($user) {
                $validKey = hash('sha256', $user->email . $user->user_id);
                if ($validKey == $key) {
                    //remove all user categories
                    $this->getUserCategoriesTable()->deleteCategoryByUserId($user->user_id);
                    //remove all user days
                    $this->getUserDaysTable()->deleteUserDaysByUserId($user->user_id);
                    $message = "You have been unsubscribed from The Joke of the Day";
                } else {
                    $message = "Invalid key - Make sure you are using the link from your email. If you are having trouble unsubscribing, send an email to mike@sendmejokes.com";
                }
            } else {
                $message = "Invalid email address - Make sure you are using the link from your email. If you are having trouble unsubscribing, send an email to mike@sendmejokes.com";
            }
        }

        return new ViewModel(array(
            'message' => $message,
        ));
    }
}