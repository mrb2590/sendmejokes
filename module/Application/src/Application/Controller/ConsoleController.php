<?php
/**
 * SendMeJokes (http://www.sendmejokes.com/)
 *
 * @author    Mike Buonomo <mike@sendmjokes.com>
 * @link      https://github.com/mrb2590/sendmejokes
 */

namespace Application\Controller;

use Application\Model\Vote;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Console\Request as ConsoleRequest;

class ConsoleController extends ApplicationController
{
    public function emailDailyJokeAction()
    {
        // Make sure that we are running in a console and the user has not tricked our
        // application into running this action from a public web server.
        $request = $this->getRequest();
        if (!$request instanceof ConsoleRequest) {
            throw new \RuntimeException('Access Denied Mother Fucker!!!!');
        }

        // Get user email from console and check if the user used --user or -u flag
        // This is for sending daily joke to a signle user
        $userEmail = $request->getParam('userEmail');
        $userFlag = $request->getParam('user') || $request->getParam('u');
        $allFlag = $request->getParam('all') || $request->getParam('a');
        $jokeCategories = $this->getJokeCategoriesTable()->fetchAll();

        if ($allFlag) {
            $users = $this->getUserTable()->fetchAll();
            foreach ($users as $user) {
                $userCategories = $this->getUserCategoriesTable()->getUserCategories($user);
                $userExcludeCategories = $this->getUserExcludeCategoriesTable()->getUserExcludeCategories($user);
                $userSentJokes = $this->getUserSentJokesTable()->getUserSentJokes($user);
                $possibleJokes = array();

                echo "Username: " . $user->username . "\n";

                //filter jokes by user categories
                foreach ($jokeCategories as $jokeCategory) {
                    foreach ($userCategories as $userCategory) {
                        if ($jokeCategory->cat_id == $userCategory->cat_id) {
                            $possibleJokes[$jokeCategory->joke_id] = true;
                            echo "Possible Joke id: " . $jokeCategory->joke_id . "   cat id: " . $jokeCategory->cat_id . "\n";
                        }
                    }
                }
                //remove any jokes that have categories from user exclude categories table
                foreach ($jokeCategories as $jokeCategory) {
                    foreach ($userExcludeCategories as $userExcludeCategory) {
                        if ($jokeCategory->cat_id == $userExcludeCategory->cat_id) {
                            if (isset($possibleJokes[$jokeCategory->joke_id])) {
                                unset($possibleJokes[$jokeCategory->joke_id]);
                                echo "Removed Joke id: " . $jokeCategory->joke_id . "\n";
                            }
                        }
                    }
                }
                //remove any jokes that have recently been sent
                foreach ($jokeCategories as $jokeCategory) {
                    foreach ($userSentJokes as $userSentJoke) {
                        if (isset($possibleJokes[$userSentJoke->joke_id]) && (strtotime($userSentJoke->sent_on) >= (strtotime('-30 days')))) {
                            unset($possibleJokes[$jokeCategory->joke_id]);
                            echo "Removed Joke id: " . $jokeCategory->joke_id . " because joke has been sent\n";
                        }
                    }
                }
                    var_dump($possibleJokes);
            }
        }
        return;
    }
}
