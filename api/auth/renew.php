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
    try {
        global $key; 
        $refresh = JWT::decode($_COOKIE['refreshJWT'], $key, array('HS256'));
        $user = new User();
        $user->setID($refresh->id);
        $user->setFirstname($refresh->firstName);
        $user->setLastname($refresh->lastName);
        $user->setPermission($refresh->permission);
        header('Set-Cookie: refreshJWT=' . $user->generateRefreshJWT() . '; httpOnly');
        echo (json_encode(array('message' => 'OK', 'access' => $user->generateAccessJWT())));
    } catch (Exception $e) {
        echo (json_encode(array("message" => "Invalid Token")));
    }
} else {
    echo (json_encode(array('message' => 'Please Login')));
}