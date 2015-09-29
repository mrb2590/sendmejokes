<?php
$config = require '../config/autoload/local.php';

//connect to db
$smj = new mysqli('localhost', $config['db']['username'], $config['db']['password'], 'sendmejokes');

if ($smj->connect_errno) {
    printf("Connect failed %s\n", $recentJokesDatabase->connect_error);
    exit;
}



//get all users
$query = "SELECT user_id FROM user";
$users = $smj->query($query);

foreach ($users as $user) {
    $categories = getUserCategories($user['user_id']);
    //only send joke if user has selected at least one category
    if ($categories) {
        $joke = findJoke($user_id, $categories);

    }
}



//returns array of user IDs
function getUserIds() {
    global $smj;
    $query = "SELECT user_id FROM user";
    return $smj->query($query);
}

//returns array of user categories
function getUserCategories($user_id) {
    global $smj;
    $query = "SELECT cat_id FROM user_categories WHERE user_id='$user_id'";
    return $smj->query($query);
}

//returns a random joke
function findJoke($user_id, $categories) {
    global $smj;
    //build the query
    $query = "SELECT joke.* from joke WHERE ";
}