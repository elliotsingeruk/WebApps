<?php
include_once("../database.php");
include_once("../auth.php");
if ($_COOKIE['jwt'] != null) {
    switch (checkJwt($_COOKIE['jwt'])) {
        case 1:
            die("You are already signed in");
            break;
        case 2:
            privNewUser();
            break;
        case 3:
            privNewUser();
            break;
    }
} else {
    if ((filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) && passwordCheck($_POST["password"], $_POST["confirmPassword"]) && checkString($_POST["firstName"]) && checkString($_POST["lastName"])) {
        echo (newUser($_POST["email"], password_hash($_POST["password"], PASSWORD_BCRYPT), $_POST["firstName"], $_POST["lastName"], 1));
    } else {
        die("Input Invalid");
    }
}

function privNewUser(){
    if ((filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) && passwordCheck($_POST["password"], $_POST["confirmPassword"]) && checkString($_POST["firstName"]) && checkString($_POST["lastName"])) {
        echo (newUser($_POST["email"], password_hash($_POST["password"], PASSWORD_BCRYPT), $_POST["firstName"], $_POST["lastName"], $_POST["permission"]));
    } else {
        die("Input Invalid");
    }
}


//add new user to the database
function newUser($email, $password, $firstName, $lastName, $permission)
{
    global $db;
    $dbEmail = $db->real_escape_string($email);
    $dbPassword = $db->real_escape_string($password);
    $dbFirstName = $db->real_escape_string($firstName);
    $dbLastName = $db->real_escape_string($lastName);
    //check to see if a user already exists with the same email
    $res = $db->query("SELECT id FROM users WHERE email = '$dbEmail'");
    $row = $res->fetch_assoc();
    //if there are no records, then add the new user
    if ($res->num_rows == 0) {
        $db->query("INSERT INTO users (email, password, firstName, lastName) VALUES ('$dbEmail', '$dbPassword', '$dbFirstName', '$dbLastName')");
        $db->query("INSERT INTO user_permission (permission_id, user_id) VALUES('$permission', (SELECT id FROM users WHERE email = '$dbEmail'))");
        return (http_response_code(200));
    } else {
        return ("User with that email exists");
    }
}
//check if string is empty
function checkString($string)
{
    return !($string == null);
}
//Check if the password and confirm passwords are fields are the same
//also check to see if the password is suitable in length
function passwordCheck($p1, $p2)
{
    if (($p1 == $p2) && (strlen($p1) > 6)) {
        return true;
    } else {
        return false;
    }
}
