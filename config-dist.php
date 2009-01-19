<?php

// Your API key
$api_key = '<your key>';

// ID of the group
$groupid = '<groupid>';

// ID of the owner of the group
$userid  = '<userid>';

// How many pics should be displayed from each member on the front page? 
$pics_per_member = 5;

// How many pics should be displayed on the page ?v=browse
$pics_per_row = 9;
$rows_per_page = 8;

// Path to phpFlickr.php - get it from http://phpflickr.com/
// Tested with version 2.2.0
$phpflickr = '/path/to/phpFlickr.php';

// Enable caching? 
$enable_cache = true;
$db_conn = 'mysql://<db_user>:<db_pass>@<db_server>/<db_name>';

// String to separate links in "menu"
$link_sep = ' &bull; ';

?>