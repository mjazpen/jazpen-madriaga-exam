<?php

$api_key ="AIzaSyBHvz0SS3qwwAsWJu5y4SzVohb7P_0L5p8";

function db_object(){
    // make a connection to mysql here using PDO
    $host = "localhost";
    $dbname = "youtube_db";
    $username = "root";
    $password = "";
    $db = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);

    return $db;
}

