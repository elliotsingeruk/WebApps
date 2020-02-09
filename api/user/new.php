<?php
require_once("../database.php");
require_once("../creds.php");

require_once '../jwt/BeforeValidException.php';
require_once '../jwt/ExpiredException.php';
require_once '../jwt/SignatureInvalidException.php';
require_once '../jwt/JWT.php';

use \Firebase\JWT\JWT;
//check if the passwords match
if ($_POST['password'] == $_POST['confirmPassword']) {
    //check if the user has already signed in
    if (isset($_COOKIE['jwt'])) {
        try {
            global $key;
            $decodedJwt = JWT::decode($_COOKIE['jwt'], $key, array('HS256'));
            if ($decodedJwt->permission == 1) {
                //if the user is logged in and has the default permissions, prevent the user from creating another account
                echo (json_encode(array('message' => 'Already Logged In')));
            } else {
                //if the user has a higher permission, then add the user
                echo (newUser($_POST["firstName"], $_POST["lastName"], $_POST["email"], $_POST["permission"], $_POST["password"]));
            }
        } catch (Exception $e) {
            echo (json_encode(array("message" => "Invalid Token")));
        }
    } else {
        //if the user is not logged in, then allow the user to be added with the default permission level of 1
        echo (newUser($_POST["firstName"], $_POST["lastName"], $_POST["email"], 1, $_POST["password"]));
    }
} else {
    echo (json_encode(array("message" => "Passwords don't match")));
}

function newUser($postFirstName, $postLastName, $email, $permission, $password)
{
    //prevent unwanted markup
    $firstName = filter_var($postFirstName, FILTER_SANITIZE_STRING);
    $lastName = filter_var($postLastName, FILTER_SANITIZE_STRING);
    //check the fields to make sure they are valid
    if(checkEmpty($firstName) && checkEmpty($lastName) && checkPassword($password) && checkEmail($email)){
        global $db;
        //prevent unwanted SQL chars
        $dbEmail = $db->real_escape_string($email);
        $dbPassword = password_hash($password, PASSWORD_BCRYPT);
        $dbFirstName = $db->real_escape_string($firstName);
        $dbLastName = $db->real_escape_string($lastName);
        //check to see if a user already exists with the same email
        $res = $db->query("SELECT id FROM users WHERE email = '$dbEmail'");
        $res->fetch_assoc();
        //if there are no records, then add the new user
        if ($res->num_rows == 0) {
            $db->query("INSERT INTO users (email, password, firstName, lastName) VALUES ('$dbEmail', '$dbPassword', '$dbFirstName', '$dbLastName')");
            $db->query("INSERT INTO user_permission (permission_id, user_id) VALUES('$permission', (SELECT id FROM users WHERE email = '$dbEmail'))");
            return (json_encode(array('message' => "OK")));
        } else {
            return (json_encode(array('message' => 'An account already exists for that email')));
        }
    } else {
        return (json_encode(array("message" => "Invalid Field")));
    }
}
function checkEmpty($str){
    return ($str != null);
}
function checkEmail($str){
    return filter_var($str, FILTER_VALIDATE_EMAIL);
}
function checkPassword($str){
    return (strlen($str) > 6);
}
function checkPerm($str){
    return ($str > 0 || $str < 4);
}