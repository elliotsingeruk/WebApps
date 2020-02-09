<?php
require_once("../database.php");
require_once("../creds.php");

require_once '../jwt/BeforeValidException.php';
require_once '../jwt/ExpiredException.php';
require_once '../jwt/SignatureInvalidException.php';
require_once '../jwt/JWT.php';

use \Firebase\JWT\JWT;
if(isset($_COOKIE["access"])){
    try{
        global $key;
        $decodedJwt = JWT::decode($_COOKIE['access'], $key, array('HS256'));
        if (!isset($_GET["id"])){
            global $db;
            $result = $db->query("SELECT firstName, lastName, email FROM users WHERE id = '$decodedJwt->id'");
            $row = $result->fetch_assoc();
            if($result->num_rows == 1){
                echo (json_encode(array('message' => 'OK', 'firstName' => $row['firstName'], 'lastName' => $row['lastName'], 'email' => $row['email'])));
            } else {
                echo (json_encode(array('message' => 'Invalid Account')));
            }
        } else if ($decodedJwt->permission > 1 && isset($_GET["id"]) && $_GET["id"] != 'all'){
            global $db;
            $id = $db->real_escape_string($_GET['id']);
            $result = $db->query("SELECT firstName, lastName, email FROM users WHERE id = '$id'");
            $row = $result->fetch_assoc();
            if($result->num_rows == 1){
                echo (json_encode(array('message' => 'OK', 'firstName' => $row['firstName'], 'lastName' => $row['lastName'], 'email' => $row['email'])));
            } else {
                echo (json_encode(array('message' => 'Invalid Account')));
            }
        } else if ($decodedJwt->permission > 1 && $_GET["id"] == 'all'){
            global $db;
            $result = $db->query("SELECT id, firstName, lastName, email FROM users");
            if ($result->num_rows > 0) {
                $rows = array();
                while($r = $result->fetch_assoc()){
                    $rows[] = array('user' => $r);
                }          
                echo json_encode(array('message' => 'OK') + $rows);
            } else {
                echo json_encode(array('message' => 'No Users'));
            }
        }
    } catch (Exception $e){
        echo (json_encode(array('message' => 'Invalid Token')));
    }
}