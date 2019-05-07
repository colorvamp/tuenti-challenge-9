<?php

function get_secret($key)
{
    foreach (list_secrets() as $secret) {
        if ($secret['name'] == $key) {
            return $secret;
        }
    }
}
function list_secrets()
{
    $db = new SQLite3('../db.sqlite');
    $result = $db->query("SELECT * from secrets");
    $rows = [];
    while ($row = $result->fetchArray()) {
        $rows[] = $row;
    }
    return $rows;
}

function get_auth_key()
{
    $secret = get_secret('auth_key');
    return $secret['content'];
}

function check_credentials($user, $pass)
{
    if (!preg_match("'^[a-zA-Z0-9_\\-]+$'", $user)) {
        return null;
    }
    if (!preg_match("'^[a-zA-Z0-9_\\-]+$'", $pass)) {
        return nill;
    }
    $db = new SQLite3('../db.sqlite');
    $row = $db->querySingle("SELECT * from users where user = '$user' and password = '$pass'", true);
    if ($row) {
        return $user;
    }
    return null;
}

function create_auth_cookie($user)
{
    $authKey = get_auth_key();
    if (!$authKey) {
        return false;
    }
    $userMd5 = md5($user, true);

    $result = '';
    for ($i = 0; $i < strlen($userMd5); $i++) {
        $result .= bin2hex(chr((ord($authKey[$i]) + ord($userMd5[$i])) % 256));
    }
    return $result;
}

function set_cookies($user)
{
    $authCookie = create_auth_cookie($user);
    setcookie('user', $user);
    setcookie('auth', $authCookie);
}

function delete_cookies()
{
    setcookie('user');
    setcookie('auth');
}

function check_cookies()
{
    $userCookie = $_COOKIE['user'];
    $authCookie = $_COOKIE['auth'];

    if ($authCookie === create_auth_cookie($userCookie)) {
        return $userCookie;
    }
    return false;
}
