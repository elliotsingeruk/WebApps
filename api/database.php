<?php
require("creds.php");
//start a new connection to the db server
$db = new mysqli("localhost", $mysqlUser, $mysqlPwd, "webapp");

if($db->connect_error){
    die("Database Connection Error");
}