<?php

include_once 'src/links.php';
include_once 'src/auth.php';

ini_set('display_errors', false);

[$section] = explode('?', urldecode(substr($_SERVER['REQUEST_URI'], 1)));

if ($section == '') {
    if (!check_cookies()) {
        include('templates/login.php');
    } else {
        include('templates/home.php');
    }
}

if ($section == 'post-login') {
    $user = $_POST['user'];
    $pass = $_POST['password'];

    $matchedUser = check_credentials($user, $pass);
    if ($user === $matchedUser) {
        set_cookies($matchedUser);
        header('Location: ' . page_link(''));
    }
    include('templates/wrong-credentials.php');
}

if ($section == 'logout') {
    delete_cookies();
    header('Location: ' . page_link(''));
}

$sectionParts = explode('/', $section);
if ($sectionParts[0] == 'secrets' && check_cookies()) {
    $secret = get_secret($sectionParts[1]);
    if ($secret) {
        if ($secret['users'] && !in_array(check_cookies(), explode(',', $secret['users']))) {
            include('templates/secret-forbidden.php');
        } else {
            include('templates/secret.php');
        }
    }
}