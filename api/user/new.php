<?php
require_once("../database.php");
require_once("../creds.php");

require_once '../jwt/BeforeValidException.php';
require_once '../jwt/ExpiredException.php';
require_once '../jwt/SignatureInvalidException.php';
require_once '../jwt/JWT.php';

use \Firebase\JWT\JWT;

if ($_POST['password'] == $_POST['confirmPassword']) {
    if (isset($_COOKIE['jwt'])) {
        try {
            global $key;
            $decodedJwt = JWT::decode($_COOKIE['jwt'], $key, array('HS256'));
            if ($decodedJwt->permission == 1) {
                echo (json_encode(array('message' => 'Already Logged In')));
            } else {
                echo (newUser($_POST["firstName"], $_POST["lastName"], $_POST["email"], $_POST["permission"], $_POST["password"]));
            }
        } catch (Exception $e) {
            echo (json_encode(array("message" => "Invalid Token")));
        }
    } else {
        echo (newUser($_POST["firstName"], $_POST["lastName"], $_POST["email"], 1, $_POST["password"]));
    }
} else {
    echo (json_encode(array("message" => "Passwords don't match")));
}

function newUser($postFirstName, $postLastName, $email, $permission, $password)
{
    $firstName = filter_var($postFirstName, FILTER_SANITIZE_STRING);
    $lastName = filter_var($postLastName, FILTER_SANITIZE_STRING);
    if(checkEmpty($firstName) && checkEmpty($lastName) && checkPassword($password) && checkEmail($email)){
        global $db;
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