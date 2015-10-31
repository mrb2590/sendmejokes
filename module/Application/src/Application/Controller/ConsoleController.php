<?php
/**
 * SendMeJokes (http://www.sendmejokes.com/)
 *
 * @author    Mike Buonomo <mike@sendmjokes.com>
 * @link      https://github.com/mrb2590/sendmejokes
 */

namespace Application\Controller;

use Application\Model\Vote;
use Application\Model\UserSentJoke;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Console\Request as ConsoleRequest;

class ConsoleController extends ApplicationController
{
    /**
     * example usage: php public/index.php emaildailyjoke -a
     */
    public function emailDailyJokeAction()
    {
        //get current day of week
        $currentDay = date('l');
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
                echo "Username: " . $user->username . "\r\n";
                echo "Email: " . $user->email . "\r\n";

                //get user days skip user if no days selected
                $userDays = $this->getUserDaysTable()->getUserDays($user);

                if ($userDays) {
                    $dayMatch = false;
                    foreach ($userDays as $userDay) {
                        $dayMatch = ($userDay->day == $currentDay) ? true : $dayMatch;
                    }
                    if (!$dayMatch) {
                        echo "User is not subscribed to recieve jokes on " . $currentDay . "s\r\n\r\n";
                        continue;
                    }
                    //get user categories and skip user if no categories are selected
                    $userCategories = $this->getUserCategoriesTable()->getUserCategories($user);
                    if ($userCategories->count() <= 0) {
                        echo "User has selected today as one of their user-days, but is not subscribed to any categories\r\n\r\n";
                        continue;
                    }
                } else {
                    echo "User is not subscribed for any days of the week\r\n\r\n";
                    continue;
                }

                $userExcludeCategories = $this->getUserExcludeCategoriesTable()->getUserExcludeCategories($user);
                $userSentJokes = $this->getUserSentJokesTable()->getUserSentJokes($user);
                $possibleJokes = array();

                //filter jokes by user categories
                foreach ($jokeCategories as $jokeCategory) {
                    foreach ($userCategories as $userCategory) {
                        if ($jokeCategory->cat_id == $userCategory->cat_id) {
                            $possibleJokes[$jokeCategory->joke_id] = true;
                            //echo "Possible Joke id: " . $jokeCategory->joke_id . "   cat id: " . $jokeCategory->cat_id . "\r\n";
                        }
                    }
                }

                //remove any jokes that have categories from user exclude categories table
                foreach ($jokeCategories as $jokeCategory) {
                    foreach ($userExcludeCategories as $userExcludeCategory) {
                        if ($jokeCategory->cat_id == $userExcludeCategory->cat_id) {
                            if (isset($possibleJokes[$jokeCategory->joke_id])) {
                                unset($possibleJokes[$jokeCategory->joke_id]);
                                //echo "Removed Joke id: " . $jokeCategory->joke_id . "\r\n";
                            }
                        }
                    }
                }

                $filteredJokes = $possibleJokes; //keep original filtered jokes incase the next filter removes them all

                //remove jokes that have recently been sent
                foreach ($jokeCategories as $jokeCategory) {
                    foreach ($userSentJokes as $userSentJoke) {
                        if (isset($possibleJokes[$userSentJoke->joke_id]) && (strtotime($userSentJoke->sent_on) >= (strtotime('-30 days')))) {
                            unset($possibleJokes[$jokeCategory->joke_id]);
                            //echo "Removed Joke id: " . $jokeCategory->joke_id . " because joke has been sent\r\n";
                        }
                    }
                }

                //get first joke from list of possible jokes
                if (!empty($possibleJokes)) {
                    $joke = $this->getJokeTable()->getJoke(current(array_keys($possibleJokes)));
                } else {
                    echo "***No jokes left after filtering recently sent jokes. Choosing a random one...\r\n";
                    $keys = array_keys($filteredJokes);
                    shuffle($keys);
                    $joke = $this->getJokeTable()->getJoke(current($keys));
                }

                //get joke categories
                $jokeCategories = $this->getJokeCategoriesTable()->getJokeCategoriesByJokeId($joke->joke_id);
                //get category names
                foreach ($jokeCategories as $jokeCategory) {
                    $categories[] = $this->getCategoryTable()->getCategory($jokeCategory->cat_id);
                }
                //get joke votes
                $votes = $this->getVoteTable()->getVotesByJoke($joke->joke_id);
                //sum up votes
                $voteSum = 0;
                $userVote = "0";
                foreach ($votes as $vote) {
                    $voteSum += (int) $vote->vote;
                    //get user's vote on this joke
                    if ($vote->user_id == $user->user_id) {
                        $userVote = $vote->vote;
                    }
                }

                //add to sent jokes table
                $sentJoke = new UserSentJoke();
                $sentJoke->joke_id = $joke->joke_id;
                $sentJoke->user_id = $user->user_id;
                $this->getUserSentJokesTable()->addUserSentJoke($sentJoke);

                //send email
                $this->sendDailyJokeEmail($user, $joke, $categories, $voteSum, $userVote);
                echo "Daily Joke Sent. Joke ID: " . $joke->joke_id . "\r\n\r\n";
            }
        }
        return;
    }

    function sendDailyJokeEmail($user, $joke, $categories, $voteSum, $userVote)
    {
        $logoSource = 'http://www.sendmejokes.com/img/logo.png';
        $key = hash('sha256', $user->user_id);
        //build categories string
        $categoriesString = '';
        foreach ($categories as $index => $category) {
            $categoriesString .= ($index != 0) ? " | " . $category->name : $category->name;
        }
        //build headers, to, & subject
        $headers  = 'From: SendMeJokes <jokes@sendmejokes.com>' . "\r\n";
        $headers .= 'Reply-To: SendMeJokes <jokes@sendmejokes.com>' . "\r\n";
        $headers .= 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
        $to       = $user->email;
        $subject  = "Daily Joke from SendMeJokes!";
        //build message
        $message  = '';
        $message .= '<html>';
        $message .= '<body style="background-color: #fff; padding: 20px; font-family: Arial;">';
        $message .=     '<div style="text-align:center; width:100%; margin-bottom:15px"><img class="logo-img" src="' . $logoSource . '" style="height:50px; width:auto"></div>';
        $message .=     '<p style="width:100%; text-align:center">Daily Joke!</p>';
        $message .=     '<div style="text-align:center; width:100%; border:1px solid #333; padding:15px">';
        $message .=         '<p>' . $joke->joke . '</p>';
        $message .=         ($joke->answer != null) ? '<p>' . $joke->answer . '</p>' : '';
        $message .=     '</div>';
        $message .=     '<div style="width:100%; height:50px; border:1px solid #333; padding:15px">';
        $message .=         '<span style="float:left; width:50%">' . $categoriesString . '</span>';
        $message .=         '<span style="float:right; width:50%">';
        $message .=             '<a href="http://dev.sendmejokes.com/jokes/email-vote?joke_id=' . $joke->joke_id . '&email=' . $user->email . '&k=' . $key . '&vote=1">UpVote</a>';
        $message .=             ' ' . $voteSum . ' ';
        $message .=             '<a href="http://dev.sendmejokes.com/jokes/email-vote?joke_id=' . $joke->joke_id . '&email=' . $user->email . '&k=' . $key . '&vote=-1">DownVote</a>';
        $message .=         '</span>';
        $message .=     '</div>';
        $message .= '</body>';
        $message .= '</html>';

        mail($to, $subject, $message, $headers);
    }
}
