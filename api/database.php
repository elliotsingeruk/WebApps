<?php
require("creds.php");
$db = new mysqli("localhost", $mysqlUser, $mysqlPwd, "webapp");

if($db->connect_error){
    die("Database Connection Error");
}

