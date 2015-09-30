<?php
$smj = connectToSMJ();
$users = getUsers();

foreach ($users as $user) {
    $userCategories = getUserCategories($user['user_id']);
    //only send joke if user has selected at least one category
    if ($userCategories) {
        $joke = findJoke($user, $userCategories);
        var_dump($joke);
        //mail joke
    }
}

//returns mysqli object
function connectToSMJ() {
    $config = require dirname(__FILE__) . '/../config/autoload/local.php';
    $smj = new mysqli('localhost', $config['db']['username'], $config['db']['password'], 'sendmejokes');
    if ($smj->connect_errno) {
        printf("Connect failed %s\n", $recentJokesDatabase->connect_error);
        exit;
    }
    return $smj;
}

//returns array of user IDs
function getUsers() {
    global $smj;
    $query = "SELECT user_id, email FROM user";
    return $smj->query($query);
}

//returns array of user categories
function getUserCategories($user_id) {
    global $smj;
    $query = "SELECT cat_id FROM user_categories WHERE user_id='$user_id'";
    return $smj->query($query);
}

//returns a random joke
function findJoke($user, $userCategories) {
    global $smj;
    //get all joke that from user categories filtering out any recent jokes sent
    $query =  "SELECT j.joke_id FROM joke j
               JOIN joke_categories jc ON j.joke_id=jc.joke_id
               JOIN user_categories uc ON jc.cat_id=uc.cat_id AND uc.user_id='{$user['user_id']}';";
    $rowset = $smj->query($query);
    //build new array 
    foreach ($rowset as $row) {
        $possibleJokes[] = $row;
    }
    //filter jokes that may also have a category user has not signed up for
    foreach($possibleJokes as $key => $possibleJoke) {
        $jokeOnlyContainsUserCategories = false;
        //get joke categories
        $query = "SELECT cat_id FROM joke_categories WHERE joke_id='{$possibleJoke['joke_id']}'";
        $possibleJokeCategories = $smj->query($query);

        $numJokeCategories = 0;
        //must equal $numJokeCategories at end of loop, otherwise there is a category that the user has not signed up for
        foreach($possibleJokeCategories as $possibleJokeCategory) {
            $numJokeCategories++;
            $UserCategoryMatch = 0;
            foreach($userCategories as $userCategory) {
                if ($possibleJokeCategory['cat_id'] == $userCategory['cat_id']) {
                    $UserCategoryMatch++;
                }
            }
            $unsetFlag = ($UserCategoryMatch == $numJokeCategories) ? false : true;
            if ($unsetFlag) {
                break;
            }
        }
        if ($unsetFlag) {
            unset($possibleJokes[$key]);
        }
    }
    //filter all recently sent jokes
    $possiblesJokesCatFiltered = $possibleJokes; //save original incase we need to choose a random joke from here
    $query = "SELECT joke_id FROM user_sent_jokes WHERE sent_on BETWEEN NOW() - INTERVAL 30 DAY AND NOW()";
    $recentlySentJokes = $smj->query($query);
    foreach($possibleJokes as $key => $possibleJoke) {
        foreach ($recentlySentJokes as $recentlySentJoke) {
            if ($recentlySentJoke['joke_id'] == $possibleJoke['joke_id']) {
                unset($possibleJokes[$key]);
                break;
            }
        }
    }
    //failsafe - if all jokes have been filtered out
    //(because theres not enough within the user categories), 
    //choose a random one to send again
    if (!$possibleJokes) {
        echo "User " . $user['email'] . " is out of jokes! Sending a radnom one.\r\n";
        shuffle($possiblesJokesCatFiltered);
        $possibleJokes = $possiblesJokesCatFiltered;
        $insert = false; //don't add to new recent jokes
    } else {
        $insert = true;
    }
    $jokeToBeSent = reset($possibleJokes);
    //add to recent jokes
    if ($insert) {
        $query = "INSERT INTO user_sent_jokes (joke_id, user_id) VALUES ({$jokeToBeSent['joke_id']}, '{$user['user_id']}')";
        $smj->query($query);
    }
    return $jokeToBeSent;
}