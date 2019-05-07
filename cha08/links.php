<?php

[$httpHost, $httpPort] = explode(':', $_SERVER['HTTP_HOST']);

define('ASSETS_HOST', "http://$httpHost:" . ($httpPort + 1));
define('PAGE_HOST', "http://$httpHost:" . ($httpPort));

function asset_link($path)
{
    return ASSETS_HOST . "/$path?goodboy";
}

function page_link($path)
{
    return PAGE_HOST . "/$path?goodboy";
}