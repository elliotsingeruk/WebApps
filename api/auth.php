<?php
include_once("database.php");
include_once("creds.php");

require_once 'jwt/BeforeValidException.php';
require_once 'jwt/ExpiredException.php';
require_once 'jwt/SignatureInvalidException.php';
require_once 'jwt/JWT.php';
use \Firebase\JWT\JWT;
//check entered email and password against values stored in database, if they match, the user can be authenticated
//and a session can be started for the user
function authenticateUser($email, $password){
    global $db;
    $inputEmail = $db->real_escape_string($email);
    $res = $db->query("SELECT id, firstName, lastName, password FROM users WHERE email = '$inputEmail'");
    $row = $res->fetch_assoc();
    if ($res->num_rows == 1) {
        if (password_verify($password, $row["password"])) {
            $tokenId = base64_encode(random_bytes(32));
            $issuedAt = time();
            $notBefore = $issuedAt + 10;             //Adding 10 seconds
            $expire = $notBefore + 604800;            // Adding 604800 seconds
            $serverName = "webapps";
            $payload = array(
                "iat" => $issuedAt,
                "jti" => $tokenId,
                "iss" => $serverName,
                "nbf" => $notBefore,
                "exp" => $expire,
                "name" => $row["firstName"] . " " . $row["lastName"],
                "id" => $row["id"]
            );
            global $key;
            setIAT($row["id"], $issuedAt);
            return ("jwt:" . JWT::encode($payload, $key));
        }
    }
    return "Invalid username or password";
}
//set the issued at time in the database so individual tokens can be invalidated
function setIAT($userID, $iat){
    global $db;
    $escapeIAT = $db->real_escape_string($iat);
    $escapeUserID = $db->real_escape_string($userID);
    $db->query("UPDATE users SET jwtIAT = '$escapeIAT' WHERE id ='$escapeUserID'");
}
//this function checks the validity of the session token stored in the browser
function checkJwt($jwt){
    global $key;
    try {
        // decode jwt
        $decoded = JWT::decode($jwt, $key, array('HS256'));
        //extract the id of the user from the token
        $jwtID = $decoded->id;
        global $db;
        //query the data base to find when the token was issued
        $res = $db->query("SELECT jwtIAT, permission_id FROM users INNER JOIN user_permission ON users.id = user_permission.user_id WHERE id = '$jwtID'");
        $row = $res->fetch_assoc();
        //if there are records held with that id
        if($res->num_rows == 1){
            //check if the decoded jwt has the same issued at time and has not expired.
            if(($decoded->iat == $row["jwtIAT"]) && ($decoded->exp > time())){
                return $row["permission_id"];
            }
        } else {
            return "Invalid session token";
        }
    }
    catch (Exception $e){
        // set response code
        return "Invalid session token";
    }
}
function decodeJwt($jwt){
    global $key;
    try{
        return(JWT::decode($jwt, $key, array('HS256')));
    } catch (Exception $e){
        return "JWT invalid";
    }
}
