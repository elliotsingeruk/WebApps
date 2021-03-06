<?php
require_once("../database.php");
require_once("../user.php");

echo(authenticateUser($_POST['email'], $_POST['password']));

function authenticateUser($postEmail, $postPassword){
    global $db;
    //prevent the email string containing SQL parameters
    $escapeEmail = $db->real_escape_string($postEmail);
    $result = $db->query("SELECT email, firstName, lastName, id, permission_id, password FROM users INNER JOIN user_permission ON users.id = user_permission.user_id WHERE email = '$escapeEmail'");
    $row = $result->fetch_assoc();
    if($result->num_rows == 1){
        //if a result is returned from the database, do the following
        if(password_verify($postPassword, $row["password"])){
            //instantiate the user object and assign the parameters to the object
            $user = new User;
            $user->setID($row["id"]);
            $user->setFirstname($row["firstName"]);
            $user->setLastname($row["lastName"]);
            $user->setEmail($postEmail);
            $user->setPermission($row["permission_id"]);
            //use the generateRefreshJWT to generate a refresh token
            $refreshJWT = $user->generateRefreshJWT();
            //set the refresh token to a http only cookie so it is not vunerable for XSS
            header("Set-Cookie: refreshJWT=" . $refreshJWT . "; httpOnly");
            //send a OK message and the access JWT token
            return json_encode(array('message' => 'OK', 'access' => $user->generateAccessJWT()));
        }
    }
    //if no user with that email exists or the password is wrong, do the following
    return json_encode(array('message' => 'Incorrect email or password'));
}