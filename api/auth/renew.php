<?php
require_once("../database.php");
require_once("../creds.php");
require_once("../user.php");
require_once '../jwt/BeforeValidException.php';
require_once '../jwt/ExpiredException.php';
require_once '../jwt/SignatureInvalidException.php';
require_once '../jwt/JWT.php';
use \Firebase\JWT\JWT;

if (isset($_COOKIE["refreshJWT"])){
    //if the refresh token exists, do the following
    try {
        //attempt to decode the refresh jwt
        global $key; 
        $refresh = JWT::decode($_COOKIE['refreshJWT'], $key, array('HS256'));
        $user = new User();
        //pass the existing user creds into the new user object
        $user->setID($refresh->id);
        $user->setFirstname($refresh->firstName);
        $user->setLastname($refresh->lastName);
        $user->setPermission($refresh->permission);
        //generate and return the new refresh and access token
        header('Set-Cookie: refreshJWT=' . $user->generateRefreshJWT() . '; httpOnly');
        echo (json_encode(array('message' => 'OK', 'access' => $user->generateAccessJWT())));
    } catch (Exception $e) {
        //if the refresh token is invalid, send a message to the client
        echo (json_encode(array("message" => "Invalid Token")));
    }
} else {
    //if no refresh token exists, then the user is not logged in
    echo (json_encode(array('message' => 'Please Login')));
}