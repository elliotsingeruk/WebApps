<?php
include_once("../auth.php");
if (filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
    echo(authenticateUser($_POST["email"], $_POST["password"]));
} else {
    die("Invalid Email");
}
