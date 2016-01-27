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
                        if (isset($possibleJokes[$userSentJoke->joke_id]) && (strtotime($userSentJoke->sent_on) >= (strtotime('-180 days')))) {
                            unset($possibleJokes[$userSentJoke->joke_id]);
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
                $currentJokeCategories = $this->getJokeCategoriesTable()->getJokeCategoriesByJokeId($joke->joke_id);
                //get category names
                foreach ($currentJokeCategories as $jokeCategory) {
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

        //build categories string
        $categoriesString = '';
        $i = 0;
        foreach ($categories as $category) {
            $anchor = '<a href="http://www.sendmejokes.com/jokes/view/' . $category->url_name . '/" style="color:#2196F3">' . $category->name . '</a>';
            $categoriesString .= ($i == 0) ? $anchor : '| ' . $anchor;
            $i++;
        }

        //build mailto link
        $mailTojoke = trim(preg_replace('/[\r|\n|\r\n]+/', '%0A', $joke->joke));
        $mailTojoke = preg_replace('/"/', '%22', $mailTojoke);
        $mailToAnswer = trim(preg_replace('/[\r|\n|\r\n]+/', '%0A', $joke->answer));
        $mailToAnswer = preg_replace('/"/', '%22', $mailToAnswer);
        $mailToSubject = '?subject=Check out this joke from SendMeJokes!';
        $mailToBody = '&amp;body=' . $mailTojoke . '%0A';
        $mailToBody .= trim(preg_replace('/\s+/', ' ', $mailToAnswer)) . '%0A%0A';
        $mailToBody .= 'See more at http:%2F%2Fwww.sendmejokes.com/jokes/';
        $mailTo = $mailToSubject . $mailToBody;

        //build fb share link
        $fbShareLink = 'https://www.facebook.com/sharer/sharer.php?u=http%3A//www.sendmejokes.com/jokes/view/' . $joke->joke_id . '/';
        //build g+ share link
        $gpShareLink = 'https://plus.google.com/share?url=http%3A//www.sendmejokes.com/jokes/view/' . $joke->joke_id . '/';
        //build smj share link
        $smjShareLink = 'http://www.sendmejokes.com/jokes/view/' . $joke->joke_id . '/';

        //build voting icon links
        $key = hash('sha256', $user->user_id);
        if ($userVote == "0") {
            $upVoteAnchor  = '<a href="http://www.sendmejokes.com/jokes/email-vote?joke_id=' . $joke->joke_id . '&email=' . $user->email . '&k=' . $key . '&vote=1">';
            $upVoteAnchor .=     '<img src="http://www.sendmejokes.com/img/icon_thumbs_o_up.png" height="20" style="height:20px;width:auto;"/>';
            $upVoteAnchor .= '</a>';
            $downVoteAnchor  = '<a href="http://www.sendmejokes.com/jokes/email-vote?joke_id=' . $joke->joke_id . '&email=' . $user->email . '&k=' . $key . '&vote=-1">';
            $downVoteAnchor .=     '<img src="http://www.sendmejokes.com/img/icon_thumbs_o_down.png" height="20" style="height:20px;width:auto;"/>';
            $downVoteAnchor .= '</a>';
        } elseif ($userVote == "1") {
            $upVoteAnchor  = '<a href="http://www.sendmejokes.com/jokes/email-vote?joke_id=' . $joke->joke_id . '&email=' . $user->email . '&k=' . $key . '&vote=0">';
            $upVoteAnchor .=     '<img src="http://www.sendmejokes.com/img/icon_thumbs_up.png" height="20" style="height:20px;width:auto;"/>';
            $upVoteAnchor .= '</a>';
            $downVoteAnchor  = '<a href="http://www.sendmejokes.com/jokes/email-vote?joke_id=' . $joke->joke_id . '&email=' . $user->email . '&k=' . $key . '&vote=-1">';
            $downVoteAnchor .=     '<img src="http://www.sendmejokes.com/img/icon_thumbs_o_down.png" height="20" style="height:20px;width:auto;"/>';
            $downVoteAnchor .= '</a>';
        } else {
            $upVoteAnchor  = '<a href="http://www.sendmejokes.com/jokes/email-vote?joke_id=' . $joke->joke_id . '&email=' . $user->email . '&k=' . $key . '&vote=1">';
            $upVoteAnchor .=     '<img src="http://www.sendmejokes.com/img/icon_thumbs_o_up.png" height="20" style="height:20px;width:auto;"/>';
            $upVoteAnchor .= '</a>';
            $downVoteAnchor  = '<a href="http://www.sendmejokes.com/jokes/email-vote?joke_id=' . $joke->joke_id . '&email=' . $user->email . '&k=' . $key . '&vote=0">';
            $downVoteAnchor .=     '<img src="http://www.sendmejokes.com/img/icon_thumbs_down.png" height="20" style="height:20px;width:auto;"/>';
            $downVoteAnchor .= '</a>';
        }

        //build optout link
        $optoutLink = "http://www.sendmejokes.com/user/unsubscribe/?k=" . hash('sha256', $user->email . $user->user_id) . '&email=' . $user->email;

        //build message
        $message  = '';
        $message .= '<html>';
        $message .= '<body style="color:#333333;background-color:#E5E5E5;padding:20px;font-family:Arial,Helvetica,sans-serif;font-size:16px;">';
        $message .=     '<div class="panel" style="width:100%;max-width:600px;background-color:#FFFFFF;border:1px solid #D8D8D8;margin:0 auto;">';
        $message .=         '<div class="panel-heading" style="background-color:#333333;color:#FFFFFF;padding:10px;border-bottom:5px solid #2196F3;text-align:center;font-size:22px;">';
        $message .=             '<img src="http://www.sendmejokes.com/img/logo.png" height="50" style="height:50px;width:auto;"/>';
        $message .=             '<br/>';
        $message .=             'The Joke of the Day #' . $joke->joke_id . ' ';
        $message .=         '</div><!-- /.panel-heading -->';
        $message .=         '<div class="panel-body" style="padding:20px;">';
        $message .=             '<p class="joke">' . $joke->joke . '</p>';
        $message .=             '<p class="answer" style="font-weight:bold">' . $joke->answer . '</p>';
        $message .=             '<p class="categories" style="font-size:12px">';
        $message .=                 'Categories: ' . $categoriesString;
        $message .=             '</p>';
        $message .=         '</div><!-- /.panel-body -->';
        $message .=         '<div class="panel-footer" style="border-top:1px solid #333;background-color:#F5F5F5;padding:20px;">';
        $message .=             '<a href="mailto:' . $mailTo . '"><img src="http://www.sendmejokes.com/img/icon_envelope.png" height="20" style="height:20px;width:auto;"/></a>';
        $message .=             '<a href="' . $fbShareLink . '"><img src="http://www.sendmejokes.com/img/icon_link_out.png" height="20" style="height:20px;width:auto;margin-left:5px;"/></a>';
        $message .=             '<a href="' . $gpShareLink . '"><img src="http://www.sendmejokes.com/img/icon_link_out2.png" height="20" style="height:20px;width:auto;margin-left:5px;"/></a>';
        $message .=             '<a href="' . $smjShareLink . '"><img src="http://www.sendmejokes.com/img/icon_external_link.png" height="20" style="height:20px;width:auto;margin-left:5px;"/></a>';
        $message .=             '<span style="float:right">';
        $message .=                 $upVoteAnchor . '<span style="font-size:20px;">' . $voteSum . '</span>' . $downVoteAnchor;
        $message .=             '</span>';
        $message .=         '</div><!-- /.panel-footer -->';
        $message .=     '</div><!-- /.panel -->';
        $message .=     '<div class="disclaimer" style="width:100%;max-width:600px;margin:0 auto;font-size:12px;text-align:center;">';
        $message .=         '<p style="color:#333333;padding:10px;">';
        $message .=             'The Joke of the Day is sent on the days you choose, around 9:00am EST. ';
        $message .=             'Unsubscribe from the Joke of the Day by <a href="' . $optoutLink . '" style="color:#2196F3">clicking here</a> ';
        $message .=             'or signing into your account and unchecking all categories or unchecking all days of the week. ';
        $message .=             'If you do not want to recieve jokes of a cetain category, sign into your account ';
        $message .=             '<a href="http://www.sendmejokes.com/" style="color:#2196F3">here</a> and update your joke preferences. ';
        $message .=             'The Joke of the Day is meant for fun and in no way to be taken seriously.';
        $message .=         '</p>';
        $message .=     '</div><!-- /.disclaimer -->';
        $message .= '</body>';
        $message .= '</html>';

        mail($to, $subject, $message, $headers);
    }
}
