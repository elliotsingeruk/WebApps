<?php
require_once("../database.php");
require_once("../creds.php");

require_once '../jwt/BeforeValidException.php';
require_once '../jwt/ExpiredException.php';
require_once '../jwt/SignatureInvalidException.php';
require_once '../jwt/JWT.php';

use \Firebase\JWT\JWT;
//not done
if(isset($_COOKIE['access'])){
    try {
        global $key;
        $decodedJwt = JWT::decode($_COOKIE['access'], $key, array('HS256'));
        if(isset($_POST["id"]) && $decodedJwt->permission > 1){
            
        } else {
            
        }
    } catch (Exception $e){
        echo (json_encode(array('message' => 'Invalid Token')));
    }
} else {
    echo (json_encode(array('message' => 'Invalid Token')));
}